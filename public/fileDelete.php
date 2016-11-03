<?php
//
// Description
// ===========
// This method will remore a file from the club.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to remove the item from.
// file_id:             The ID of the file to remove.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_newsletters_fileDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'file_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'File'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'newsletters', 'private', 'checkAccess');
    $rc = ciniki_newsletters_checkAccess($ciniki, $args['business_id'], 'ciniki.newsletters.fileDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the uuid of the newsletters item to be deleted
    //
    $strsql = "SELECT uuid FROM ciniki_newsletter_files "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.newsletters', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.newsletters.7', 'msg'=>'Unable to find existing item'));
    }
    $uuid = $rc['file']['uuid'];

    //
    // Remove the file from storage
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'storageFileDelete');
    $rc = ciniki_core_storageFileDelete($ciniki, $args['business_id'], 'ciniki.newsletters.file', array(
        'uuid'=>$uuid));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Delete the file
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    return ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.newsletters.file', $args['file_id'], $uuid, 0x07);
}
?>
