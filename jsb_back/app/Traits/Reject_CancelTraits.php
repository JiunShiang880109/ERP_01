<?php

namespace App\Traits;

use DB;
trait Reject_CancelTraits
{ //作廢
    public function cancel_MIG($data)
    {
        DB::connection('mysql');
        $value['rejectNum'] = $data['rejectNum'];
        $rejectDetail = DB::select("SELECT * FROM reject WHERE rejectNum=?", [$value['rejectNum']]);
        $value['rejectDetail'] = $rejectDetail;
        $value['invoiceNumber'] = $rejectDetail[0]->invoiceNumber;
        $value['invoiceDate'] = $rejectDetail[0]->invoiceDate;
        $value['buyerTax'] = $rejectDetail[0]->buyerTax;
        $value['sellerTax'] = $rejectDetail[0]->sellerTax;
        $value['cancelDate'] = $rejectDetail[0]->cancelDate;
        $value['cancelTime'] = $rejectDetail[0]->cancelTime;
        $value['cancelReason'] = $rejectDetail[0]->cancelReason;
    }
    //B2B
    public function cancel_XMLA0501($value)
    {

        $filed = '<?xml version="1.0" encoding="utf-8"?>
        <CancelInvoice xsi:schemaLocation="urn:GEINV:eInvoiceMessage:A0501:3.2 A0501.xsd" xmlns="urn:GEINV:eInvoiceMessage:A0501:3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <CancelInvoiceNumber>' .$value['invoiceNumber'];
        $filed = $filed.' </CancelInvoiceNumber>
          <InvoiceDate>' . $value['invoiceDate'];
        $filed = $filed.'</InvoiceDate>

          <BuyerId>' .$value['buyerTax'];
        $filed = $filed.'</BuyerId>

          <SellerId>' . $value['sellerTax'];
        $filed = $filed.'</SellerId>

          <CancelDate>' .$value['cancelDate'];
        $filed = $filed.'</CancelDate>

          <CancelTime>' .$value['cancelTime'];
        $filed = $filed.'</CancelTime>


          <CancelReason>' .$value['cancelReason'];
        $filed =$filed.'</CancelReason>
        </CancelInvoice>';

        //return $filed;
        $xml = new \SimpleXMLElement($filed);
        $current = date("Ymd");

        $file_path = '../../dalimarket/image/mig/' . $current;
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/mig/' . $current . '/A0501';
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/invoice/' . $value['rejectNum'];
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file = '../../dalimarket/image/invoice/' . $value['rejectNum'] . '/' . $value['rejectNum'] . '_A0501.xml';

        $xml->asXml($file);
        $files = '../../dalimarket/image/mig/' . $current . '/A0501/' . $value['invoiceNumber'] . '.xml';

        $xml->asXml($files);
        return $filed;
    }
    //B2C
    public function cancel_XMLC0501($value)
    {


      $filed = '<?xml version="1.0" encoding="utf-8"?>
      <CancelInvoice xsi:schemaLocation="urn:GEINV:eInvoiceMessage:C0501:3.2 C0501.xsd" xmlns="urn:GEINV:eInvoiceMessage:C0501:3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <CancelInvoiceNumber>' .$value['invoiceNumber'];
      $filed = $filed.' </CancelInvoiceNumber>
        <InvoiceDate>' . $value['invoiceDate'];
      $filed = $filed.'</InvoiceDate>

        <BuyerId>' .$value['buyerTax'];
      $filed = $filed.'</BuyerId>

        <SellerId>' . $value['sellerTax'];
      $filed = $filed.'</SellerId>

        <CancelDate>' .$value['cancelDate'];
      $filed = $filed.'</CancelDate>

        <CancelTime>' .$value['cancelTime'];
      $filed = $filed.'</CancelTime>


        <CancelReason>' .$value['cancelReason'];
      $filed =$filed.'</CancelReason>
      </CancelInvoice>';

      //return $filed;
      $xml = new \SimpleXMLElement($filed);
      $current = date("Ymd");

      $file_path = '../../dalimarket/image/mig/' . $current;
      if (!file_exists($file_path)) {

          mkdir($file_path, 0777, true);
      }
      $file_path = '../../dalimarket/image/mig/' . $current . '/C0501';
      if (!file_exists($file_path)) {

          mkdir($file_path, 0777, true);
      }
      $file_path = '../../dalimarket/image/invoice/' . $value['rejectNum'];
      if (!file_exists($file_path)) {

          mkdir($file_path, 0777, true);
      }
      $file = '../../dalimarket/image/invoice/' . $value['rejectNum'] . '/' . $value['rejectNum'] . '_C0501.xml';

      $xml->asXml($file);
      $files = '../../dalimarket/image/mig/' . $current . '/C0501/' . $value['invoiceNumber'] . '.xml';

      $xml->asXml($files);
      return $filed;
  }

}
