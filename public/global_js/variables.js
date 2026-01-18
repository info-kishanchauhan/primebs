/**
 * Created by mahendran on 6/27/15.
 */

var btnSave=$("#btnSave");
var btnBack=$("#btnBack");
var btnNew=$("#btnNew");

var spanRefNo=$("#spanRefNo");
var refno=$("#refno");

var spanRefNo1=$("#spanRefNo1");
var refno1=$("#refno1");

var widForm=$("#widForm");
var widGrid=$("#widGrid");
var clsLanguages=$(".clsLanguages");

var strActionMode="ADD";
var iActiveID;
var oTable;

//For Access Controls  1- true 0-false

var acl_ADD="0";
var acl_EDIT="0";
var acl_DELETE="0";
var acl_VIEW="0";
var acl_PRINT="0";


//Global Variables for Manage details records

var btnAddList=$("#btnAddList");
var cboProduct=$("#cboProduct");
var spanAddListTEXT=$("#AddListTEXT");
var txtQty=$("#txtQty");
var iEditDetailKeyID=0;
var objMyDetailRecords=[];
var bEditDetailRecord;
var pmDeleteLine=1;
var tblDetailsListBody=$("#tblDetailsListBody");



var btnAddList2=$("#btnAddList2");
var cboProduct2=$("#cboProduct2");
var spanAddListTEXT2=$("#AddListTEXT2");
var txtQty2=$("#txtQty2");
var iEditDetailKeyID2=0;
var objMyDetailRecords2=[];
var bEditDetailRecord2;
var pmDeleteLine2=1;
var tblDetailsListBody2=$("#tblDetailsListBody2");



var btnAddList3=$("#btnAddList3");
var cboProduct3=$("#cboProduct3");
var spanAddListTEXT3=$("#AddListTEXT3");
var txtQty3=$("#txtQty3");
var iEditDetailKeyID3=0;
var objMyDetailRecords3=[];
var bEditDetailRecord3;
var pmDeleteLine3=1;
var tblDetailsListBody3=$("#tblDetailsListBody3");

//Global Variables to store the calculation counts

var iGrossAmountTotal=0;
var iDiscountAmountTotal=0;
var iTAXAmountTotal=0;
var iTAX2AmountTotal=0;
var iNetAmountTotal=0;
var iExltaxAmount =0;

