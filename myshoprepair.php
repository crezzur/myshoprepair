<?php
include('config/config.inc.php');

    // Version checker
    $curv = '0.0.2';
    $dcheck = false;
    $fcheck = false;
    $tab = 1;
    $v = file_get_contents("https://crezzur.com/versioncheck.php", false);
    $v = json_decode($v, true);
    if ($v['version'] != $curv) { $fcheck = true; }

    function setDebug()
    {
        if (is_readable('config/defines.inc.php')) {
            $s = (debugMode() == 'true') ? 'false' : 'true';
            $file = _PS_ROOT_DIR_ . '/config/defines.inc.php';
            $cleanedFileContent = php_strip_whitespace($file);
            $fileContent = Tools::file_get_contents($file);
            if (!preg_match('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', $cleanedFileContent)) {
                return array('status' => 'danger', 'msg' => "Unable to find `define('_PS_MODE_DEV_', ".debugMode().")` in defines.inc.php !");
            }
            $fileContent = preg_replace('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', 'define(\'_PS_MODE_DEV_\', ' . $s . ');', $fileContent);
            if (!@file_put_contents($file, $fileContent)) {
                return array('status' => 'danger', 'msg' => 'The file `config/defines.inc.php is not writeable!');
            }
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file);
            }
            return array('status' => 'success', 'msg' => 'Debug modus successfully set to <b>' . $s . '</b>');
        } else {
            return array('status' => 'danger', 'msg' => 'The file `config/defines.inc.php is not readable!');
        }
    }

    function debugMode()
    {
        $clean = '';
        $path = _PS_ROOT_DIR_ . '/config/defines.inc.php';
        if (!preg_match('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', $clean, $value)) {
            $clean = php_strip_whitespace($path);
            if (!preg_match('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', $clean, $value)) {
                return false;
            }
        }
        return strtolower($value[1]);
    }

    function deleteCache($target) {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            foreach( $files as $file ){
                deleteCache( $file );
            }
            rmdir($target);
        } elseif(is_file($target)) {
            unlink($target);  
        }
    }

    if(Tools::isSubmit('changeDebug')) {
        if ($dcheck == false) {
            $msg = setDebug();
        } else {
            $msg = array('status' => 'info', 'msg' => 'myShopRepair is in demo modus, changes where not saved!');
        }
            $tab = 3;
    }
    if(Tools::isSubmit('generateHtaccess')) {
        if ($dcheck == false) {
            Tools::generateHtaccess();
            $msg = array('status' => 'success', 'msg' => '.htaccess successfully updated!');
        } else {
            $msg = array('status' => 'info', 'msg' => 'myShopRepair is in demo modus, changes where not saved!');
        }
        $tab = 3;
    }
    if(Tools::isSubmit('deleteCache')) {
        if ($dcheck == false) {
            deleteCache('var/cache/');
            mkdir('var/cache', 0755);
            $msg = array('status' => 'success', 'msg' => 'Cache cleared successfully!');
        } else {
            $msg = array('status' => 'info', 'msg' => 'myShopRepair is in demo modus, changes where not saved!');
        }
        $tab = 3;
    }
    if(Tools::isSubmit('savedatabase')) {
        if ($dcheck == false) {
            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'configuration SET value = '. (int)Tools::getValue('maina') .' WHERE name = "PS_SHOP_ENABLE"');
            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'configuration SET value = "'. pSQL(Tools::getValue('mainp')) .'" WHERE name = "PS_MAINTENANCE_IP"');

            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'shop_url SET domain = "'. pSQL(Tools::getValue('shopa')) .'",
            domain_ssl = "'. pSQL(Tools::getValue('shopb')) .'", physical_uri = "'. pSQL(Tools::getValue('shopc')) .'" WHERE id_shop = 1');

            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'configuration SET value = "'. pSQL(Tools::getValue('ssld')) .'" WHERE name = "PS_SHOP_DOMAIN_SSL"');
            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'configuration SET value = '. (int)Tools::getValue('ssla') .' WHERE name = "PS_SSL_ENABLED"');
            Db::getInstance()->execute('UPDATE '. _DB_PREFIX_ .'configuration SET value = '. (int)Tools::getValue('sslb') .' WHERE name = "PS_SSL_ENABLED_EVERYWHERE"');
            $msg = array('status' => 'success', 'msg' => 'Prestashop MYSQL Database values successfully updated!');
        }  else {
            $msg = array('status' => 'info', 'msg' => 'myShopRepair is in demo modus, changes where not saved!');
        }
        $tab = 3;
    }

    $maina = Db::getInstance()->getValue('SELECT value FROM '. _DB_PREFIX_ .'configuration WHERE name = "PS_SHOP_ENABLE"');
    if ($dcheck == false) {
        $mainp = Db::getInstance()->getValue('SELECT value FROM '. _DB_PREFIX_ .'configuration WHERE name = "PS_MAINTENANCE_IP"');
    } else {
        $mainp = 'mydemoip, myseconddemoip';
    }
    $shopa = Db::getInstance()->getValue('SELECT domain FROM '. _DB_PREFIX_ .'shop_url WHERE id_shop = 1');
    $shopb = Db::getInstance()->getValue('SELECT domain_ssl FROM '. _DB_PREFIX_ .'shop_url WHERE id_shop = 1');
    $shopc = Db::getInstance()->getValue('SELECT physical_uri FROM '. _DB_PREFIX_ .'shop_url WHERE id_shop = 1');
    $ssld = Db::getInstance()->getValue('SELECT value FROM '. _DB_PREFIX_ .'configuration WHERE name = "PS_SHOP_DOMAIN_SSL"');
    $ssla = Db::getInstance()->getValue('SELECT value FROM '. _DB_PREFIX_ .'configuration WHERE name = "PS_SSL_ENABLED"');
    $sslb = Db::getInstance()->getValue('SELECT value FROM '. _DB_PREFIX_ .'configuration WHERE name = "PS_SSL_ENABLED_EVERYWHERE"');

    class PhpPsInfo {
    const TYPE_OK = true;
    const TYPE_ERROR = false;
    const TYPE_WARNING = null;

    protected $requirements = [
        'versions' => [
            'php' => '7.1',
            'mysql' => '5.5',
        ],
        'extensions' => [
            'curl' => true,
            'dom' => true,
            'fileinfo' => true,
            'gd' => true,
            'imagick' => false,
            'iconv' => true,
            'intl' => true,
            'json' => true,
            'openssl' => true,
            'mbstring' => true,
            'memcache' => false,
            'memcached' => false,
            'pdo_mysql' => true,
            'zip' => true,
            'bcmath' => false,
        ],
        'config' => [
            'allow_url_fopen' => true,
            'expose_php' => false,
            'file_uploads' => true,
            'max_input_vars' => 1000,
            'memory_limit' => '64M',
            'post_max_size' => '16M',
            'register_argc_argv' => false,
            'set_time_limit' => true,
            'short_open_tag' => false,
            'upload_max_filesize' => '4M',
        ],
        'directories' => [
            'cache_dir' => 'var/cache',
            'log_dir' => 'var/logs',
            'img_dir' => 'img',
            'mails_dir' => 'mails',
            'module_dir' => 'modules',
            'translations_dir' => 'translations',
            'customizable_products_dir' => 'upload',
            'virtual_products_dir' => 'download',
            'config_sf2_dir' => 'app/config',
            'translations_sf2' => 'app/Resources/translations',
        ],
        'apache_modules' => [
            'mod_rewrite' => true,
        ],
    ];

    protected $recommended = [
        'versions' => [
            'php' => '7.3',
            'mysql' => '5.6',
        ],
        'extensions' => [
            'curl' => true,
            'dom' => true,
            'fileinfo' => true,
            'gd' => true,
            'imagick' => true,
            'iconv' => true,
            'intl' => true,
            'json' => true,
            'openssl' => true,
            'mbstring' => true,
            'memcache' => false,
            'memcached' => true,
            'pdo_mysql' => true,
            'zip' => true,
            'bcmath' => true,
        ],
        'config' => [
            'allow_url_fopen' => true,
            'expose_php' => false,
            'file_uploads' => true,
            'max_input_vars' => 5000,
            'memory_limit' => '256M',
            'post_max_size' => '128M',
            'register_argc_argv' => false,
            'set_time_limit' => true,
            'short_open_tag' => false,
            'upload_max_filesize' => '128M',
        ],
        'apache_modules' => [
            'mod_rewrite' => true,
        ],
    ];

    public function getVersions() {
        $data = [
            'Web server' => [ $this->getWebServer() ],
            'PHP Type' => [
                strpos( PHP_SAPI, 'cgi' ) !== false ?
                'CGI with Apache Worker or another webserver' :
                'Apache Module (low performance)'
            ],
        ];

        $data[ 'PHP Version' ] = [
            $this->requirements[ 'versions' ][ 'php' ],
            $this->recommended[ 'versions' ][ 'php' ],
            PHP_VERSION,
            version_compare( PHP_VERSION, $this->recommended[ 'versions' ][ 'php' ], '>=' ) ?
            self::TYPE_OK : (
                version_compare( PHP_VERSION, $this->requirements[ 'versions' ][ 'php' ], '>=' ) ?
                self::TYPE_WARNING :
                self::TYPE_ERROR
            )
        ];

        if ( !extension_loaded( 'mysqli' ) || !is_callable( 'mysqli_connect' ) ) {
            $data[ 'MySQLi Extension' ] = [
                true,
                true,
                'Not installed',
                self::TYPE_ERROR,
            ];
        } else {
            $data[ 'MySQLi Extension' ] = [
                $this->requirements[ 'versions' ][ 'mysql' ],
                $this->recommended[ 'versions' ][ 'mysql' ],
                mysqli_get_client_info(),
                self::TYPE_OK,
            ];
        }

        $data[ 'Internet connectivity (Prestashop)' ] = [
            false,
            true,
            gethostbyname( 'www.prestashop.com' ) !== 'www.prestashop.com',
            gethostbyname( 'www.prestashop.com' ) !== 'www.prestashop.com',
        ];

        return $data;
    }

    public function getPhpExtensions() {
        $data = [];
        $vars = [
            'BCMath Arbitrary Precision Mathematics' => 'bcmath',
            'Client URL Library (Curl)' => 'curl',
            'Image Processing and GD' => 'gd',
            'Image Processing (ImageMagick)' => 'imagick',
            'Human Language and Character Encoding Support (Iconv)' => 'iconv',
            'Internationalization Functions (Intl)' => 'intl',
            'Memcache' => 'memcache',
            'Memcached' => 'memcached',
            'Multibyte String (Mbstring)' => 'mbstring',
            'OpenSSL' => 'openssl',
            'File Information (Fileinfo)' => 'fileinfo',
            'JavaScript Object Notation (Json)' => 'json',
            'PDO and MySQL Functions' => 'pdo_mysql',
        ];
        foreach ( $vars as $label => $var ) {
            $value = extension_loaded( $var );
            $data[ $label ] = [
                $this->requirements[ 'extensions' ][ $var ],
                $this->recommended[ 'extensions' ][ $var ],
                $value
            ];
        }

        $vars = [
            'PHP-DOM and PHP-XML' => [ 'dom', 'DomDocument' ],
            'Zip' => [ 'zip', 'ZipArchive' ],
        ];
        foreach ( $vars as $label => $var ) {
            $value = class_exists( $var[ 1 ] );
            $data[ $label ] = [
                $this->requirements[ 'extensions' ][ $var[ 0 ] ],
                $this->recommended[ 'extensions' ][ $var[ 0 ] ],
                $value
            ];
        }

        return $data;
    }

    public function getPhpConfig() {
        $data = [];
        $vars = [
            'allow_url_fopen',
            'expose_php',
            'file_uploads',
            'register_argc_argv',
            'short_open_tag',
        ];
        foreach ( $vars as $var ) {
            $value = ( bool )ini_get( $var );
            $data[ $var ] = [
                $this->requirements[ 'config' ][ $var ],
                $this->recommended[ 'config' ][ $var ],
                $value
            ];
        }

        $vars = [
            'max_input_vars',
            'memory_limit',
            'post_max_size',
            'upload_max_filesize',
        ];
        foreach ( $vars as $var ) {
            $value = ini_get( $var );
            if ( $this->toBytes( $value ) >= $this->toBytes( $this->recommended[ 'config' ][ $var ] ) ) {
                $result = self::TYPE_OK;
            } elseif ( $this->toBytes( $value ) >= $this->toBytes( $this->requirements[ 'config' ][ $var ] ) ) {
                $result = self::TYPE_WARNING;
            } else {
                $result = self::TYPE_ERROR;
            }

            $data[ $var ] = [
                $this->requirements[ 'config' ][ $var ],
                $this->recommended[ 'config' ][ $var ],
                $value,
                $result,
            ];
        }

        $vars = [
            'set_time_limit',
        ];
        foreach ( $vars as $var ) {
            $value = is_callable( $var );
            $data[ $var ] = [
                $this->recommended[ 'config' ][ $var ],
                $this->requirements[ 'config' ][ $var ],
                $value
            ];
        }

        return $data;
    }

    public function getDirectories() {
        $data = [];
        foreach ( $this->requirements[ 'directories' ] as $directory ) {
            $directoryPath = getcwd() . DIRECTORY_SEPARATOR . trim( $directory, '\\/' );
            $data[ $directory ] = [ file_exists( $directoryPath ) && is_writable( $directoryPath ) ];
        }

        return $data;
    }

    public function getServerModules() {
        $data = [];
        if ( $this->getWebServer() !== 'Apache' || !function_exists( 'apache_get_modules' ) ) {
            return $data;
        }

        $modules = apache_get_modules();
        $vars = array_keys( $this->requirements[ 'apache_modules' ] );
        foreach ( $vars as $var ) {
            $value = in_array( $var, $modules );
            $data[ $var ] = [
                $this->requirements[ 'apache_modules' ][ $var ],
                $this->recommended[ 'apache_modules' ][ $var ],
                $value,
            ];
        }

        return $data;
    }

    public function toBytes( $value ) {
        if ( is_numeric( $value ) ) {
            return $value;
        }

        $value = trim( $value );
        $val = ( int )$value;
        switch ( strtolower( $value[ strlen( $value ) - 1 ] ) ) {
            case 'g':
                $val *= 1024;
                // continue
            case 'm':
                $val *= 1024;
                // continue
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public function toString( $value ) {
        if ( $value === true ) {
            return 'Yes';
        } elseif ( $value === false ) {
            return 'No';
        } elseif ( $value === null ) {
            return 'N/A';
        }

        return strval( $value );
    }

    public function toHtmlClass( array $data ) {
        if ( count( $data ) === 1 && !is_bool( $data[ 0 ] ) ) {
            return 'table-info';
        }


        if ( count( $data ) === 1 && is_bool( $data[ 0 ] ) ) {
            $result = $data[ 0 ];
        } elseif ( array_key_exists( 3, $data ) ) {
            $result = $data[ 3 ];
        } else {
            if ( $data[ 2 ] >= $data[ 1 ] ) {
                $result = self::TYPE_OK;
            } elseif ( $data[ 2 ] >= $data[ 0 ] ) {
                $result = self::TYPE_WARNING;
            } else {
                $result = self::TYPE_ERROR;
            }
        }

        if ( $result === false ) {
            return 'table-danger';
        }

        if ( $result === null ) {
            return 'table-warning';
        }

        return 'table-success';
    }

    protected function getWebServer() {
        if ( stristr( $_SERVER[ 'SERVER_SOFTWARE' ], 'Apache' ) !== false ) {
            return 'Apache';
        } elseif ( stristr( $_SERVER[ 'SERVER_SOFTWARE' ], 'LiteSpeed' ) !== false ) {
            return 'Lite Speed';
        } elseif ( stristr( $_SERVER[ 'SERVER_SOFTWARE' ], 'Nginx' ) !== false ) {
            return 'Nginx';
        } elseif ( stristr( $_SERVER[ 'SERVER_SOFTWARE' ], 'lighttpd' ) !== false ) {
            return 'lighttpd';
        } elseif ( stristr( $_SERVER[ 'SERVER_SOFTWARE' ], 'IIS' ) !== false ) {
            return 'Microsoft IIS';
        }

        return 'Not detected';
    }

    protected function commandExists( $command ) {
        $which = ( PHP_OS == 'WINNT' ) ? 'where' : 'which';

        $process = proc_open(
            $which . ' ' . $command, [
                [ 'pipe', 'r' ], //STDIN
                [ 'pipe', 'w' ], //STDOUT
                [ 'pipe', 'w' ], //STDERR
            ],
            $pipes
        );

        if ( $process !== false ) {
            $stdout = stream_get_contents( $pipes[ 1 ] );
            $stderr = stream_get_contents( $pipes[ 2 ] );
            fclose( $pipes[ 1 ] );
            fclose( $pipes[ 2 ] );
            proc_close( $process );

            return $stdout != '';
        }

        return false;
    }
}
    $info = new PhpPsInfo();
?>

<!doctype html>
<html class="h-100" lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
<meta name="description" content=""/>
<meta name="author" content="https://crezzur.com"/>
<link rel="icon" href="https://crezzur.com/img/crezzur-logo-1544601440.jpg"/>
<title>myShopRepair - <?php echo 'v'.$curv; ?></title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" rel="stylesheet" />
<style>
body {
    overflow-x: hidden;
}
label {
    font-size: 14px;
}
.card-profile {
    margin-top: 30px;
    text-align: center;
}
.card {
    display: inline-block;
    position: relative;
    width: 100%;
    margin-bottom: 30px;
    border-radius: 6px;
    color: rgba(0, 0, 0, 0.87);
    background: #fff;
    box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.2), 0 1px 5px 0 rgba(0, 0, 0, 0.12);
}
.card-profile .card-avatar, .card-testimonial .card-avatar {
    max-width: 130px;
    max-height: 130px;
    margin: -50px auto 0;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 16px 38px -12px rgba(0, 0, 0, 0.56), 0 4px 25px 0px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2);
}
.card img {
    width: 100%;
    height: auto;
}
.card .ftr {
    margin-top: 15px;
}
.btn.btn-crez, .navbar .navbar-nav > li > a.btn.btn-crez {
    background-color: #4693cf;
    color: #fff;
    box-shadow: 0 2px 2px 0 rgba(234, 76, 137, 0.14), 0 3px 1px -2px rgba(234, 76, 137, 0.2), 0 1px 5px 0 rgba(234, 76, 137, 0.12);
}
.btn.btn-just-icon, .navbar .navbar-nav > li > a.btn.btn-just-icon {
    font-size: 20px;
    padding: 12px 12px;
    line-height: 1em;
}
.btn.btn-round, .navbar .navbar-nav > li > a.btn.btn-round {
    border-radius: 30px;
}
</style>
</head>
<body class="h-100">
<div id="page-container" class="container-fluid pr-0 pl-0 h-100 d-flex flex-column">
<?php 
    if ($dcheck == true) { 
        echo '<div class="alert alert-info text-center font-weight-bold" role="alert">You are viewing a demo version, changes will not be saved.</div>';
    } if ($fcheck == true) { 
        echo '<div class="alert alert-danger text-center font-weight-bold" role="alert">You are using version '.$curv.' of myShopRepair which is outdated! Please update your version of myShopRepair to version '.$v['version'].'.</div>';
    } if (isset($msg)) {
        echo '<div class="alert alert-'.$msg['status'].' mx-5 mt-3 alert-dismissible fade show" role="alert">'.$msg['msg'].'
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    }
?>
<div class="pt-4">
    <nav>
        <div class="nav nav-tabs pl-4" id="nav-tab" role="tablist">
            <a class="nav-item nav-link <?php if($tab == 1) { echo 'active'; } ?>" id="nav-welcome-tab" data-toggle="tab" href="#nav-welcome" role="tab" aria-controls="nav-welcome" aria-selected="true">Welcome</a>
            <a class="nav-item nav-link <?php if($tab == 2) { echo 'active'; } ?>" id="nav-info-tab" data-toggle="tab" href="#nav-info" role="tab" aria-controls="nav-info" aria-selected="false">Installation requirements</a>
            <a class="nav-item nav-link <?php if($tab == 3) { echo 'active'; } ?>" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Settings</a>
            <a class="nav-item nav-link <?php if($tab == 4) { echo 'active'; } ?>" id="nav-log-tab" data-toggle="tab" href="#nav-log" role="tab" aria-controls="nav-loh" aria-selected="false">Changelog</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">

        <div class="tab-pane fade <?php if($tab == 1) { echo 'show active'; } ?>" id="nav-welcome" role="tabpanel" aria-labelledby="nav-welcome-tab">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-10">
                        <div class="card card-profile">
                            <div class="card-avatar"> <a target="_blank" href="https://crezzur.com"> <img class="img" src="https://crezzur.com/img/logocrezzur150.png"> </a> </div>
                            <div class="table">
                                <h4 class="card-caption pt-3">myShopRepair</h4>
                                <h6 class="category text-muted">Version <?php echo $curv; ?></h6>
                                <h5 class="pt-3">Welkom Prestashop user!</h5>
                                This page is created by <a target="_blank" href="https://www.prestashop.com/forums/profile/840493-crezzur/">Crezzur</a> and maintained with the contribution of Prestashop members. <br>
                                If you notice a bug or are in need of an update you can post your request on the Prestashop topic <a href="https://www.prestashop.com/forums/topic/1032900-tool-myshoprepair/">here</a>. <br>
                                <div class="text-left px-5 py-4 text-muted">
                                <b>Tab: Installation requirements</b><br>
                                This tab will allow you to check if your server is setup correctly.<br>
                                We will check if your php.ini is serup correctly, that your folders and files have the right writing permisson, ...<br>
                                <br>
                                <b>Tab: Settings</b><br>
                                This tab will allow you to make some changes without the need to connect to your MYSQL database our FTP Client.<br>
                                Abilty to make changes in MYSQL database, remove cache, ...
                                </div>
                                <div class="ftr"> <a target="_blank" href="https://crezzur.com" class="btn btn-just-icon btn-crez btn-round" data-toggle="tooltip" data-placement="top" title="Visit our website"> <i class="fas fa-link"></i> </a> <a target="_blank" href="https://crezzur.com/contact-us" class="btn btn-just-icon btn-crez btn-round" data-toggle="tooltip" data-placement="top" title="Contact us"> <i class="fas fa-envelope"></i> </a> <a target="_blank" href="https://www.prestashop.com/forums/topic/1032900-tool-myshoprepair/" class="btn btn-just-icon btn-crez btn-round pt-2" data-toggle="tooltip" data-placement="top" title="Go to Prestashop topic"> <img alt="" src="https://crezzur.com/img/presta.png"> </a> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <div class="card card-profile mt-0">
                            <div class="table text-left px-4 pt-3">
                                <b>Contributors: </b><br>
                                <a target="_blank" href="https://www.prestashop.com/forums/profile/840493-crezzur/">Crezzur</a>,
                                <a target="_blank" href="https://github.com/PierreRambaud">PierreRambaud</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="tab-pane fade <?php if($tab == 2) { echo 'show active'; } ?>" id="nav-info" role="tabpanel" aria-labelledby="nav-info-tab">
            <div class="row justify-content-md-center">
                <div class="text-muted pt-2">This page is a copy of <a target="_blank" href="https://github.com/PrestaShop/php-ps-info">Github php-ps-info</a> - author: <a target="_blank" href="https://github.com/PierreRambaud">PierreRambaud</a></div>
                <main role="main" class="col-md-10 pt-3">
                    <h4>General information & PHP/MySQL Version</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm text-center table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th>Required</th>
                                    <th>Recommended</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info->getVersions() as $label => $data) : ?>
                                <?php if (count($data) === 1) : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>" colspan="3"><?php echo $info->toString($data[0]) ?></td>
                                </tr>
                                <?php else : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td><?php echo $info->toString($data[0]) ?></td>
                                    <td><?php echo $info->toString($data[1]) ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>"><?php echo $info->toString($data[2]) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <h4>PHP Configuration</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm text-center table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th>Required</th>
                                    <th>Recommended</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info->getPhpConfig() as $label => $data) : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td><?php echo $info->toString($data[0]) ?></td>
                                    <td><?php echo $info->toString($data[1]) ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>"><?php echo $info->toString($data[2]) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <h4>PHP Extensions</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm text-center table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th>Required</th>
                                    <th>Recommended</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info->getPhpExtensions() as $label => $data) : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td><?php echo $info->toString($data[0]) ?></td>
                                    <td><?php echo $info->toString($data[1]) ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>"><?php echo $info->toString($data[2]) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <h4>Directories</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm text-center table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th>Is Writable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info->getDirectories() as $label => $data) : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>"><?php echo $info->toString($data[0]) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($info->getServerModules()) > 0): ?>
                    <h4>Apache Modules</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm text-center table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th>Required</th>
                                    <th>Recommended</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info->getServerModules() as $label => $data) : ?>
                                <tr>
                                    <td class="text-left"><?php echo $label ?></td>
                                    <td><?php echo $info->toString($data[0]) ?></td>
                                    <td><?php echo $info->toString($data[1]) ?></td>
                                    <td class="<?php echo $info->toHtmlClass($data); ?>"><?php echo $info->toString($data[2]) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    
        <div class="tab-pane fade <?php if($tab == 3) { echo 'show active'; } ?>" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
            <div class="row mt-5">
                <div class="col-md-1"></div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">Settings Overview</div>
                        <div class="card-body">
                            <h5 class="card-title m-b-0">Options:</h5>
                            <form id="settings" action="?" method="post"></form>
                                <table class="table table-bordered">
                                    <tr>
                                        <td class="col-md-10">Using the <b>update</b> button will regenerate your <b>.htaccess</b> file with the latest Prestashop settings.</td>
                                        <td class="col-md-2 text-center"><button class="btn btn-info" form="settings" name="generateHtaccess">Update</button></td>
                                    </tr>
                                    <tr>
                                        <td>Using the <b>delete</b> button will remove all <b>cache</b> files inside the <i>var/cache/...</i> folder.</td>
                                        <td class="text-center">
                                            <button class="btn btn-warning" form="settings" name="deleteCache">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>In order to display errors you need to set your debugging mode to true.
                                            Your shop is currently <b><?php echo (debugMode() == 'true') ? 'in' : 'not in' ?></b> debugging modus.
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-<?php echo (debugMode() == 'true') ? 'warning' : 'success' ?>" form="settings" name="changeDebug"><?php echo (debugMode() == 'true') ? 'Disable debug' : 'Enable debug' ?></button>
                                        </td>
                                    </tr>
                                </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">Prestashop MYSQL Database</div>
                        <div class="card-body">
<form id="savedatabase" action="?" method="post">
                            <h5 class="card-title text-right">Shop Maintenance</h5>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="ssl">Enable Shop</label>
                                <div class="col-md-8">
                                    <select class="form-control form-control-sm" name="maina">
                                        <option value="0" <?php echo ($maina == 0) ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo ($maina == 1) ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="ssl">Maintenance IP</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="mainp" value="<?php echo $mainp; ?>">
                                </div>
                            </div>

                            <h5 class="card-title text-right border-top pt-2">Set shop URL</h5>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="domain">Shop domain</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="shopa" value="<?php echo $shopa; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="ssl">SSL domain</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="shopb" value="<?php echo $shopb; ?>">
                                </div>
                            </div>
                            <div class="form-group row pb-2">
                                <label class="control-label col-md-4 text-right" for="uri">Base URI</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="shopc" value="<?php echo $shopc; ?>">
                                </div>
                            </div>

                            <h5 class="card-title text-right border-top pt-2">MYSQL - ps_configuration:</h5>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="domain">PS_SHOP_DOMAIN_SSL</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="ssld" value="<?php echo $ssld; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="control-label col-md-4 text-right" for="ssl">Enable SSL</label>
                                <div class="col-md-8">
                                    <select class="form-control form-control-sm" name="ssla">
                                        <option value="0" <?php echo ($ssla == 0) ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo ($ssla == 1) ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-2">
                                <label class="control-label col-md-4 text-right" for="uri">Enable SSL on all pages</label>
                                <div class="col-md-8">
                                    <select class="form-control form-control-sm" name="sslb">
                                        <option value="0" <?php echo ($sslb == 0) ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo ($sslb == 1) ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
</form>
                        </div>
                            <div class="card-footer text-right"><button class="btn btn-info" form="savedatabase" name="savedatabase">Save</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php if($tab == 4) { echo 'show active'; } ?>" id="nav-log" role="tabpanel" aria-labelledby="nav-log-tab">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-header">Information about latest updates and bugfixes</div>
                            <div class="card-body text-left px-5 py-4 text-muted">
                                <b>Version 0.0.2 - Release 03/10/2020</b><br>
                                03/10/2020 - Added option to enable and disable debug mode with a button click using myShopRepair tool.<br>
                                03/10/2020 - Update to an improved messaging system.<br>
                                <br>
                                <b>Version 0.0.1 - Release 01/10/2020</b><br>
                                01/10/2020 - Release date of version 0.0.1 of the tool myShopRepair
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
    
            <!-- Footer -->
            <footer class="d-flex justify-content-center mt-auto text-center" style="background-color: #f7f7f7;">
                <div class="my-3">
                    © <?php echo date('Y') ?> - myShopRepair version <?php echo $curv; ?> brought to you by <a href="https://crezzur.com/">Crezzur</a>
                </div>
            </footer>
            <!-- END Footer -->
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script> 
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> 
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
</body>
</html>
