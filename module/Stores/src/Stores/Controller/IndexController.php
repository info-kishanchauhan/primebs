<?php
namespace Stores\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }
    
    public function getrecAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $storeTable = new TableGateway('tbl_store', $adapter);
            $rowset = $storeTable->select(array('id' => $iID));
            $rowset = $rowset->toArray();
            
            foreach ($rowset as &$record) {
                // Add image preview HTML
                if (!empty($record['image'])) {
                    $imagePath = 'public/img/store2/' . $record['image'];
                    $record['image_preview'] = '<img src="' . $imagePath . '" class="store-image" alt="' . $record['name'] . '">';
                } else {
                    $record['image_preview'] = '<div class="no-store-image"><i class="fa fa-image"></i></div>';
                }
            }
            
            $result['data'] = $rowset;
            $result['DBStatus'] = 'OK';
            echo json_encode($result);
            exit;
        }
    }
    
    public function deleteAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $storeTable = new TableGateway('tbl_store', $adapter);
            
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
                
                // Delete image file if exists
                $rowset = $storeTable->select(array('id' => $iMasterID));
                $rowset = $rowset->toArray();
                if (!empty($rowset[0]['image']) && file_exists('./public/img/store2/' . $rowset[0]['image'])) {
                    unlink('./public/img/store2/' . $rowset[0]['image']);
                }
                
                $storeTable->delete(array("id=" . $iMasterID));
                $result['DBStatus'] = 'OK';
                echo json_encode($result);
                exit;
            }
        }
    }
    
    public function saveAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $storeTable = new TableGateway('tbl_store', $adapter);
            
            // Handle file upload
            $files = $request->getFiles()->toArray();
            $imageName = '';
            
            if (!empty($files['image']['name'])) {
                $imageName = $this->uploadImage($files['image']);
            }
            
            if ($request->getPost("pAction") == "ADD") {
                $aData = json_decode($request->getPost("FORM_DATA"), true);
                 unset($aData['MASTER_KEY_ID']);
                if ($imageName) {
                    $aData['image'] = $imageName;
                }
                
                $storeTable->insert($aData);
                $iMasterID = $storeTable->getLastInsertValue();
                
                $result['DBStatus'] = 'OK';
                $result['MY_ID'] = $iMasterID;
                
            } else if ($request->getPost("pAction") == "EDIT") {
                $aData = json_decode($request->getPost("FORM_DATA"), true);
                $iMasterID = $aData['MASTER_KEY_ID'];
                unset($aData['MASTER_KEY_ID']);
                
                // Get existing image
                $existingRecord = $storeTable->select(array('id' => $iMasterID));
                $existingRecord = $existingRecord->toArray();
                
                if ($imageName) {
                    // Delete old image if exists
                    if (!empty($existingRecord[0]['image']) && file_exists('./public/img/store2/' . $existingRecord[0]['image'])) {
                        unlink('./public/img/store2/' . $existingRecord[0]['image']);
                    }
                    $aData['image'] = $imageName;
                } else {
                    // Keep existing image if no new image uploaded
                    $aData['image'] = $existingRecord[0]['image'];
                }
                
                $storeTable->update($aData, array("id=".$iMasterID));
                $result['DBStatus'] = 'OK';
                $result['MY_ID'] = $iMasterID;
            }
        } else {
            $result['DBStatus'] = 'ERR';
        }
        
        echo json_encode($result);
        exit;
    }
    
    private function uploadImage($file)
    {
        $uploadDir = './public/img/store2/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'store_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($extension);
        $uploadFile = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array(strtolower($extension), $allowedTypes)) {
            return false;
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2097152) {
            return false;
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $fileName;
        }
        
        return false;
    }
    
    public function validateduplicateAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $value = $request->getPost('KEY_ID');
            
            $storeTable = new TableGateway('tbl_store', $adapter);
            $rowset = $storeTable->select(array('name' => $value));
            $rowset = $rowset->toArray();
            
            if (count($rowset) > 0) {
                $result1['recordsTotal'] = count($rowset);
                $result1['DBStatus'] = 'ERR';
                echo json_encode($result1);
            }
        }
        exit;
    }
    
    public function fnGrid()
    {
        /*
        * Script:    DataTables server-side script for PHP and MySQL
        * Copyright: 2010 - Allan Jardine, 2012 - Chris Wright
        * License:   GPL v2 or BSD (3-point)
        */
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = array('id','image','name');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_store";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['user']       = $arrDBInfo['username'];
    $gaSql['password']   = $arrDBInfo['password'];
    $gaSql['db']         = $arrDBInfo['db'];
    $gaSql['server']     = $arrDBInfo['host'];
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    /*
     * Local functions
     */
    /*
     * MySQL connection
     */
    $customObj = $this->CustomPlugin();
    $mysqli=$customObj->dbconnection();
	$sl = $this->getServiceLocator();        
	$adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
    /*
     * Paging
     */
    $sLimit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
            intval( $_GET['iDisplayLength'] );
    }
    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'desc' : 'desc') .", ";
            }
        }
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }

    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = "";
    if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
            {
                $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string($_GET['sSearch_'.$i])."%' ";
        }
    }
	
	//Add deleted_flag
    if($sWhere=="")
        $sWhere=" where 1=1";
    else
        $sWhere.=" AND  1=1";
        
        // SQL query
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $aColumns) . "
            FROM $sTable
            $sWhere
            $sOrder
            $sLimit
        ";
        
        $rResult = $mysqli->query($sQuery);
        
        // Data set length after filtering
        $sQuery = "SELECT FOUND_ROWS()";
        $rResultFilterTotal = $mysqli->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->fetch_array();
        $iFilteredTotal = $aResultFilterTotal[0];
        
        // Total data set length
        $sQuery = "SELECT COUNT($sIndexColumn) FROM $sTable";
        $rResultTotal = $mysqli->query($sQuery);
        $aResultTotal = $rResultTotal->fetch_array();
        $iTotal = $aResultTotal[0];
        
        // Output
        $output = array(
            "sEcho" => intval(@$_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        while ($aRow = $rResult->fetch_array()) {
            $row = array();
            
            // ID (hidden)
            $row[] = $aRow['id'];
            
            // Image column
            if (!empty($aRow['image'])) {
                $imagePath = 'public/img/store2/' . $aRow['image'];
                $row[] = '<img src="' . $imagePath . '" class="store-image" alt="' . $aRow['name'] . '">';
            } else {
                $row[] = '<div class="no-store-image"><i class="fa fa-image"></i></div>';
            }
            
            // Name column
            $row[] = $aRow['name'];
            
            // Action column
            $row[] = '<div style="white-space:nowrap;">
                        <button type="button" class="btn btn-list edit" row-id="'.$aRow['id'].'">
                            <span class="glyphicon glyphicon-pencil"></span> Edit
                        </button>
                       
                      </div>';
            
            $output['aaData'][] = $row;
        }
        
        $mysqli->close();
        echo json_encode($output);
    }
}