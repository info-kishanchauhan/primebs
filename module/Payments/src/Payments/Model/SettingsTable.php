<?php

namespace Payments\Model;

use Zend\Db\TableGateway\TableGateway;

class SettingsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getAlbum($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }


    public function save($data)
    {

        $id = (int) @$data['MASTER_KEY_ID'];
        if ($id == 0) {

            $this->tableGateway->insert($data);
        } else {
                unset($data['MASTER_KEY_ID']);
                $this->tableGateway->update($data, array('id' => $id));
        }
    }

    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}