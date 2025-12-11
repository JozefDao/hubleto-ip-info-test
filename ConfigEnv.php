<?php

$config['release'] = trim(@file_get_contents('release')) ?? '';

ini_set('display_errors', 1);
ini_set("error_reporting", E_ALL ^ E_DEPRECATED);

$config['sessionSalt'] = '58c292c4357d2ebdc2af59fe5833a476445f';

$config['accountUid'] = '58c292c4357d2ebdc2af59fe5833a476445f';
$config['accountFullName'] = 'My Company';

// dirs

$config['projectFolder'] = __DIR__;
$config['releaseFolder'] = ".";
$config['logFolder'] = __DIR__ . '/log';
$config['uploadFolder'] = __DIR__ . '/upload';

// urls
$config['rewriteBase'] = "/";
$config['projectUrl'] = 'http://localhost:8000/';
$config['assetsUrl'] = 'http://localhost:8000/vendor/hubleto/assets';
$config['uploadUrl'] = 'http://localhost//upload';

// sanitize dirs and urls based on used release
$config['releaseFolder'] = str_replace('__RELEASE__', $config['release'], $config['releaseFolder']);
$config['assetsUrl'] = str_replace('__RELEASE__', $config['release'], $config['assetsUrl']);

// db
$config['db_host'] = '127.0.0.1';
$config['db_user'] = 'root';
$config['db_password'] = '';
$config['db_name'] = 'hubleto_dev';
$config['db_codepage'] = 'utf8mb4';
$config['global_table_prefix'] = '';

// smtp
$config['smtpHost'] = '';
$config['smtpPort'] = '';
$config['smtpEncryption'] = '';
$config['smtpLogin'] = '';
$config['smtpPassword'] = '';

// misc
$config['develMode'] = TRUE;
$config['language'] = 'en';
$config['premiumRepoFolder'] = '';
$config['externalAppsRepositories'] = [
  'MyCompany' => __DIR__ . '/apps/external/MyCompany'
];

$config['env'] = 'local-env';
