<?php

namespace Terms\Controller;

use Zend\Db\TableGateway\TableGateway;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class IndexController extends AbstractActionController
{
    protected $studentTable;

    public function indexAction()
    {

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
        $recs=array();
        if ($request->isPost()) {

            $iID = $request->getPost("KEY_ID");


            $projectTable = new TableGateway('tbl_terms', $adapter);

            $rowset = $projectTable->select(array('id' => $iID));

            $rowset = $rowset->toArray();

            foreach ($rowset as $record)
                $recs[] = $record;

            $result['data'] = $recs;
            $result['recordsTotal'] = count($recs);
            $result['DBStatus'] = 'OK';

            $result = json_encode($result);
            echo $result;
            exit;

        }
    }

    public function  deleteAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {

            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');

            $projectTable = new TableGateway('tbl_terms', $adapter);

            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");

                $projectTable->delete(array("id=" . $iMasterID));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
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

            $projectTable = new TableGateway('tbl_terms', $adapter);


    if($request->getPost("pAction") == "ADD")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		unset($aData['MASTER_KEY_ID']);

        $aData['created_by']=$_SESSION['user_id'];
        $aData['created_on']=date("Y-m-d h:i:s");

        $projectTable->insert($aData);
        $result['DBStatus'] = 'OK';
    }
    else  if($request->getPost("pAction") == "EDIT")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
        $iMasterID=$aData['MASTER_KEY_ID'];
        unset($aData['MASTER_KEY_ID']);

        $aData['updated_by']=$_SESSION['user_id'];

        $projectTable->update($aData,array("id=".$iMasterID));
        $result['DBStatus'] = 'OK';
    }

        }
        else
        {
            $result['DBStatus'] = 'ERR';
        }

        $result = json_encode($result);
        echo $result;

        exit;
    }

    public function validateduplicateAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $request = $this->getRequest();

        if ($request->isPost()) {

            $tableName=$request->getPost('tableName');
            $ID=$request->getPost('KEY_ID');
            $fieldName=$request->getPost('fieldName');


            $sql = "select * from $tableName where $fieldName='".$ID."'";

            $optionalParameters = array();
            $statement = $adapter->createStatement($sql, $optionalParameters);

            $result = $statement->execute();
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            $rowset = $resultSet->toArray();

            if(count($rowset)>0) {

                $result1['recordsTotal'] = count($rowset);
                $result1['DBStatus'] = 'ERR';

                $result1 = json_encode($result1);
                echo $result1;
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
    $aColumns = array( 'id','refno','name','description' );

    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";

    /* DB table to use */
    $sTable = "tbl_terms";

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


   $customObj = $this->CustomPlugin();
   $mysqli=$customObj->dbconnection();

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
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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


    /*
     * SQL queries
     * Get data to display
     */
    $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
    ";
    $rResult = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );

    /* Data set length after filtering */
    $sQuery = "
        SELECT FOUND_ROWS()
    ";
    $rResultFilterTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];

    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];


    /*
     * Output
     */
    $output = array(
        "sEcho" => intval(@$_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    while ( $aRow = mysqli_fetch_array( $rResult ) )
    {
        $row = array();
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
            }
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        $output['aaData'][] = $row;
    }

    echo json_encode( $output );

}

public   function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }

    public function gettermsAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $sql="select id,name from tbl_terms order by id desc";

        $optionalParameters=array();
        $statement = $adapter->createStatement($sql, $optionalParameters);

        $result = $statement->execute();
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $rowset=$resultSet->toArray();

        $result1['DBData'] = $rowset;
        $result1['recordsTotal'] = count($rowset);
        $result1['DBStatus'] = 'OK';

        $result = json_encode($result1);
        echo $result;
        exit;

    }


}//End Class


