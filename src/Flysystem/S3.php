<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\S3.
 */

namespace Drupal\flysystem_s3\Flysystem;

use Aws\S3\S3Client;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\AwsS3v2\AwsS3Adapter;

/**
 * Drupal plugin for the "S3" Flysystem adapter.
 *
 * @Adapter(id = "s3v2")
 */
class S3 implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The S3 bucket.
   *
   * @var string
   */
  protected $bucket;

  /**
   * The path prefix inside the bucket.
   *
   * @var string
   */
  protected $prefix;

  /**
   * Options to pass into \League\Flysystem\AwsS3v2\AwsS3Adapter.
   *
   * @var array
   */
  protected $options;

  /**
   * Constructs a S3v2 object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->bucket = $configuration['bucket'];
    $this->prefix = isset($configuration['prefix']) ? $configuration['prefix'] : '';
    $this->options = !empty($configuration['options']) ? $configuration['options'] : [];

    unset(
      $configuration['bucket'],
      $configuration['prefix'],
      $configuration['options']
    );

    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    $client = S3Client::factory($this->configuration);
    return new AwsS3Adapter($client, $this->bucket, $this->prefix, $this->options);
  }

}
