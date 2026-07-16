<?php
require 'app/Config/config.php';
require 'app/Core/Database.php';
require 'app/Core/Controller.php';
require 'app/Middleware/RoleMiddleware.php';
require 'modules/Manager/ManagerController.php';
$c = new ManagerController();
$c->apiDispatchSrDetails('2');
