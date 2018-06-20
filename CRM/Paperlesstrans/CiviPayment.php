<?php

class CRM_Paperlesstrans_CiviPayment {

  var $recurId      = NULL;
  //var $firstPayment = NULL;
  //var $lastPayment  = NULL;
  var $recur        = NULL;

  /**
   * @param int $recurId
   */
  public function __construct($recurId) {
    $recur = new CRM_Contribute_DAO_ContributionRecur();
    $recur->id = $recurId;
    if ($recur->find(TRUE)) {
      $this->recur   = $recur;
      $this->recurId = $recur->id;
      //$this->getFirstPayment();
      //$this->getLastPayment();
      $this->getPaymentCount();
    }
  }

  public function isPaymentDue() {
    if ($this->recurId) {
      CRM_Core_Error::debug_var('$this->recur', $this->recur);
      CRM_Core_Error::debug_var('$this->paymentCount', $this->paymentCount);
      if ($this->recur->end_date || $this->recur->cancel_date) {
        return FALSE;
      }
      if ($this->recur->installments && ($this->paymentCount >= $this->recur->installments)) {
        return FALSE;
      }
      $offset   = "+{$this->recur->frequency_interval} {$this->recur->frequency_unit}";
      $now      = strtotime(CRM_Utils_Date::currentDBDate());
      $nextTime = strtotime($this->recur->start_date);
      CRM_Core_Error::debug_var('$this->recur->start_date', $this->recur->start_date);
      //$lastTime = strtotime($this->lastPayment->receive_date);

      // It's possible that any payment was delayed, and even though it's
      // time for payment, it won't be considered, if we base on last payment
      // receive date. E.g 1st Jan, 1st Feb, 15th Mar, 1st Apr.
      // To counter such problems, we consider recur start date as starting point
      for ($i = 1; $i <= $this->paymentCount; $i++) {
        CRM_Core_Error::debug_var('$i', $i);
        $nextTime = strtotime($offset, $nextTime);
        CRM_Core_Error::debug_var('$nextTime', $nextTime);
        $readableNextTime = date('l dS \o\f F Y h:i:s A', $nextTime);
        CRM_Core_Error::debug_var('$readableNextTime', $readableNextTime);
      }
      CRM_Core_Error::debug_var('$now', $now);
      CRM_Core_Error::debug_var('$nextTime', $nextTime);
      CRM_Core_Error::debug_var('$$now >= $nextTime', $now >= $nextTime);
      return ($now >= $nextTime);
    }
    return FALSE;
  }
  //public function getFirstPayment() {
  //  // fixme: status generalize
  //  $query = "
  //    SELECT c.*
  //    FROM  civicrm_contribution c
  //    INNER JOIN civicrm_contribution_recur r on c.contribution_recur_id = r.id
  //    WHERE r.id = %1 AND c.contribution_status_id = 1
  //    ORDER BY c.receive_date LIMIT 1";
  //  $firstPayment = CRM_Core_DAO::executeQuery($query, array(1 => array($this->recur->id, 'Positive')));
  //  if ($firstPayment->fetch()) {
  //    $this->firstPayment = $firstPayment;
  //  }
  //  return $this->firstPayment;
  //}

  //public function getLastPayment() {
  //  // fixme: status generalize
  //  $query = "
  //    SELECT c.*
  //    FROM  civicrm_contribution c
  //    INNER JOIN civicrm_contribution_recur r on c.contribution_recur_id = r.id
  //    WHERE r.id = %1 AND c.contribution_status_id = 1
  //    ORDER BY c.receive_date DESC LIMIT 1";
  //  $lastPayment = CRM_Core_DAO::executeQuery($query, array(1 => array($this->recur->id, 'Positive')));
  //  if ($lastPayment->fetch()) {
  //    $this->lastPayment = $lastPayment;
  //  }
  //  return $this->lastPayment;
  //}

  public function getPaymentCount() {
    // fixme: status generalize
    $query = "
      SELECT count(c.id)
      FROM  civicrm_contribution c
      INNER JOIN civicrm_contribution_recur r on c.contribution_recur_id = r.id
      WHERE r.id = %1 AND c.contribution_status_id = 1";
    $this->paymentCount = CRM_Core_DAO::singleValueQuery($query, array(1 => array($this->recur->id, 'Positive')));
    return $this->paymentCount;
  }
}
