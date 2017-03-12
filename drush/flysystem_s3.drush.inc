<?php

function flysystem_s3_drush_command() {

  $items = [];

  $items['sync-to'] = [
    'description' => 'Sync local files to S3 bucket.',
    'arguments' => [
       'from' => 'local directory or schema',
       'to' => 'schema for S3'
    ],
    'examples' => [
       'drush sync-to private:// s3://',
       'drush sync-to /some/dir s3://',
    ]
  ];

  $items['copy-from'] = [
    'description' => 'Copy S3 objects to local.',
    'arguments' => [
       'from' => 'schema for S3',
       'to' => 'local directory or schema'
    ],
    'examples' => [
       'drush copy-from s3:// private://',
       'drush copy-from s3:// /some/dir'
    ]
  ];

  return $items;
}



function drush_flysystem_s3_sync_to($from, $to) {

  $fsm = Drupal::service('file_system');

  $dir = $fsm->realpath($from);

  $files = file_scan_directory($dir, '//');

  foreach($files as $fname => $fvalue) {
    $a = str_replace($dir, '', $fname);
    file_unmanaged_copy($fname, $to . $a);
  }

}


function drush_flysystem_s3_copy_from($from, $to) {

  $fsm = Drupal::service('file_system');
  $dir = $fsm->realpath($to);

  if (!$dir) {
    $dir = './' . $to;
  }

  $files = file_scan_directory($from, '//');

  foreach($files as $fname => $fvalue) {

    $a = str_replace($from, '', $fname);
    $d = $dir . '/' . dirname($a);
    if (!file_exists($d)) {
      mkdir($d, 0755, true);
    }
    file_unmanaged_copy($fname, $dir . '/' . $a);
  }

}

