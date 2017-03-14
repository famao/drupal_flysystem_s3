<?php

namespace Drupal\flysystem_s3\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use League\Flysystem\Util;
use League\Flysystem\Util\MimeType;


use GuzzleHttp\Psr7\StreamWrapper;
use GuzzleHttp\Psr7\CachingStream;

/**
 * Overrides methods so it works with Drupal.
 */
class S3Adapter extends AwsS3Adapter {

  /**
   * {@inheritdoc}
   */
  public function has($path) {
    $location = $this->applyPathPrefix($path);

    if ($this->s3Client->doesObjectExist($this->bucket, $location)) {
      return TRUE;
    }

    // Check for directory existance.
    return $this->s3Client->doesObjectExist($this->bucket, $location . '/');
  }


  /**
   * Read a file as a stream.
   *
   * @param string $path
   *
   * @return array|false
   */
  public function readStream($path)
  {

      $response = $this->readObject($path);

      if ($response !== false) {
         
          $contents = $response['contents'];
          if ($response['size'] <= 1024) {
             $stream = fopen('php://memory', 'r+');
             fwrite($stream, $contents->getContents());
             rewind($stream); 
             $response['stream'] = $stream;
          } else {
             // $response['stream'] = StreamWrapper::getResource($response['contents']);
             $c = new CachingStream($contents);
             $stream = StreamWrapper::getResource($c);
             $response['stream'] = StreamWrapper::getResource($c);
             fread($stream, 1);
             rewind($stream);
             $response['stream'] = $stream;
          }
          unset($response['contents']);
      }

      return $response;
  }


  /**
   * {@inheritdoc}
   */
  public function getMetadata($path) {
    $metadata = parent::getMetadata($path);

    if ($metadata === FALSE) {
      return [
        'type' => 'dir',
        'path' => $path,
        'timestamp' => REQUEST_TIME,
        'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
      ];
    }

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  protected function upload($path, $body, Config $config) {
    $key = $this->applyPathPrefix($path);
    $options = $this->getOptionsFromConfig($config);
    $acl = isset($options['ACL']) ? $options['ACL'] : 'private';

    if (!isset($options['ContentType'])) {
      if (is_string($body)) {
        $options['ContentType'] = Util::guessMimeType($path, $body);
      }
      else {
        $options['ContentType'] = MimeType::detectByFilename($path);
      }
    }

    if (!isset($options['ContentLength'])) {
      $options['ContentLength'] = is_string($body) ? Util::contentSize($body) : Util::getStreamSize($body);
    }

    $this->s3Client->upload($this->bucket, $key, $body, $acl, ['params' => $options]);

    return $this->normalizeResponse($options, $key);
  }

}
