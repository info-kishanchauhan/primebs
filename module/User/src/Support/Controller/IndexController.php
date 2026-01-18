<?php
namespace Support\Controller;
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
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$projectTable = new TableGateway('tbl_settings', $adapter);
        $rowset = $projectTable->select();
		$rowset = $rowset->toArray();
		
		$viewModel = new ViewModel(array(
			'SUPPORT' => $rowset[0]['support_form']
		));
		
		return $viewModel;
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
		$en_key = "#&$sdfdfs789fs9w";
		
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_label', $adapter);
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
   
 
}//End Class