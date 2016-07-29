<?php

namespace Drupal\twelvestepmigrate\Plugin\migrate\source;

use Drupal\twelvestepmigrate\Plugin\migrate\source\TwelveStepIntergroupSourceTrait;
use Drupal\weeklytime\Plugin\Field\FieldType\WeeklyTimeField;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\migrate\Row;

/**
 * Generic source for TwelveStepIntergroup JSON.
 *
 * @code
 * source:
 *   plugin: twelvestepintergroup_json
 *   keys:
 *     id: [ address, city, state, postal_code, country ]
 *     day: day
 *   path: path/to/json/meetings.json
 * @endcode
 *
 * @MigrateSource(
 *   id = "twelvestepintergroup_json"
 * )
 */
class TwelveStepIntergroupJSON extends SourcePluginBase {

  use TwelveStepIntergroupSourceTrait;

  protected $iterator;

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'TwelveStepIntergroupJSON::' . $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array_keys($this->iterator->current());
  }

  /**
   * @inheritdoc}
   */
  public function initSource() {
    // Read the entire file into memory.
    if (isset($this->configuration['json-data'])) {
      // The file was already read into memory and is in the configuration.
      // @see twelvestepmigrate.drush.inc
      $data = $this->configuration['json-data'];
    }
    else {
      // @todo: read this in a more modern way.
      // @todo: allow URL style paths.
      $data = @file_get_contents($this->configuration['path']);
      $data = Json::Decode($data);
    }
    $rows = new \ArrayObject($data);
    $this->iterator = $rows->getIterator();
  }

  /**
   * @inheritdoc}
   */
  public function nextSourceRow() {
    // Get the current row and move to the next one.
    if (!$row = $this->iterator->current()) {
      return NULL;
    }
    $this->iterator->next();

    // Replace 'null' in the source data with the empty string.
    array_map(function($v) {
      return is_string($v) && $v == 'null' ? '' : $v;
    }, $row);

    return $row;
  }

  /**
   * Change the default configuration path.
   *
   * @param $path
   */
  public function setJsonData($json_data) {
    $this->configuration['json-data'] = $json_data;
  }

}
