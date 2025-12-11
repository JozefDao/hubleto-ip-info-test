<?php

namespace Hubleto\App\Custom\IpInfoTest\Controllers;

class Settings extends \Hubleto\Erp\Controller
{

  public function getBreadcrumbs(): array
  {
    return array_merge(parent::getBreadcrumbs(), [
      [ 'url' => 'ipinfotest', 'content' => 'IpInfoTest' ],
      [ 'url' => 'settings', 'content' => 'Settings' ],
    ]);
  }

  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@Hubleto:App:Custom:IpInfoTest/Settings.twig');
  }

}