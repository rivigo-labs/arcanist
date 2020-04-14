<?php

final class ArcanistRevisionRef
  extends ArcanistRef
  implements
    ArcanistDisplayRefInterface {

  const HARDPOINT_COMMITMESSAGE = 'ref.revision.commitmessage';

  private $parameters;
  private $sources = array();

  public function getRefDisplayName() {
    return pht('Revision %s', $this->getMonogram());
  }

  protected function newHardpoints() {
    return array(
      $this->newHardpoint(self::HARDPOINT_COMMITMESSAGE),
    );
  }

  public static function newFromConduit(array $dict) {
    $ref = new self();
    $ref->parameters = $dict;
    return $ref;
  }

  public static function newFromConduitQuery(array $dict) {
    // Mangle an older "differential.query" result to look like a modern
    // "differential.revision.search" result.

    $status_name = idx($dict, 'statusName');

    switch ($status_name) {
      case 'Abandoned':
      case 'Closed':
        $is_closed = true;
        break;
      default:
        $is_closed = false;
        break;
    }

    $dict['fields'] = array(
      'uri' => idx($dict, 'uri'),
      'title' => idx($dict, 'title'),
      'authorPHID' => idx($dict, 'authorPHID'),
      'status' => array(
        'name' => $status_name,
        'closed' => $is_closed,
      ),
    );

    return self::newFromConduit($dict);
  }

  public function getMonogram() {
    return 'D'.$this->getID();
  }

  public function getStatusDisplayName() {
    return idxv($this->parameters, array('fields', 'status', 'name'));
  }

  public function isClosed() {
    return idxv($this->parameters, array('fields', 'status', 'closed'));
  }

  public function getURI() {
    $uri = idxv($this->parameters, array('fields', 'uri'));

    if ($uri === null) {
      // TODO: The "uri" field was added at the same time as this callsite,
      // so we may not have it yet if the server is running an older version
      // of Phabricator. Fake our way through.

      $uri = '/'.$this->getMonogram();
    }

    return $uri;
  }

  public function getFullName() {
    return pht('%s: %s', $this->getMonogram(), $this->getName());
  }

  public function getID() {
    return (int)idx($this->parameters, 'id');
  }

  public function getPHID() {
    return idx($this->parameters, 'phid');
  }

  public function getName() {
    return idxv($this->parameters, array('fields', 'title'));
  }

  public function getAuthorPHID() {
    return idxv($this->parameters, array('fields', 'authorPHID'));
  }

  public function addSource(ArcanistRevisionRefSource $source) {
    $this->sources[] = $source;
    return $this;
  }

  public function getSources() {
    return $this->sources;
  }

  public function getCommitMessage() {
    return $this->getHardpoint(self::HARDPOINT_COMMITMESSAGE);
  }

  public function getDisplayRefObjectName() {
    return $this->getMonogram();
  }

  public function getDisplayRefTitle() {
    return $this->getName();
  }

}
