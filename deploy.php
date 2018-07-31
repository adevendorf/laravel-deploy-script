<?php

// Laravel Deploy Script
// This script assumes your .env will be located at /var/www/.env

$GIT_REPO = "https://username@bitbucket.org/project/project.git";
$VERSIONS_TO_KEEP = 5;
$APP_PATH = "/var/www/releases";
$STORAGE_PATH = "/var/www/storage";
$LIVE_PATH = "/var/www/current";
$DEPLOY_PATH = $APP_PATH . '/' . $NEW_VERSION;
$DIRS = [];
$NEW_VERSION = time();

$commands = [
    "git clone " . $GIT_REPO . " " . $DEPLOY_PATH, // Clone into a deployment path
    "rm -rf " . $DEPLOY_PATH . "/storage", // remove any preexisitng storage directory in the repo
    "ln -s " . $STORAGE_PATH . " " . $DEPLOY_PATH . "/storage", // symlink a central storage path to deployment directory
    "composer install -d " . $DEPLOY_PATH, 
    "cp /var/www/.env " . $DEPLOY_PATH, // copy a central .env to the new release
    "php " . $DEPLOY_PATH . "/artisan migrate --force", 
    "php " . $DEPLOY_PATH . "/artisan cache:clear", 
    "rm -f " . $LIVE_PATH, // remove existing symlink
    "ln -s " . $DEPLOY_PATH . " " . $LIVE_PATH, // create a new symlink
    "service php7.0-fpm reload", 
    "service nginx reload"
];

// ------------------------------------------


print(' -> Existing Releases' . "\r\n");

foreach(glob($APP_PATH . '/*', GLOB_ONLYDIR) as $dir) {
    $dir = str_replace('directory/', '', $dir);
    $DIRS[] = $dir;
    print('    ' . $dir . "\r\n");
}

print("\r\n" . "\r\n");

print(' -> Creating Release: ' . $NEW_VERSION . "\r\n");

mkdir($DEPLOY_PATH);

print("\r\n" . "\r\n");

print(' -> Removing Old Releases' . "\r\n");

if (count($DIRS) > $VERSIONS_TO_KEEP ) {
    for ($i = 0; $i < (count($DIRS) - $VERSIONS_TO_KEEP ); $i++) {
        shell_exec('rm -rf ' . $DIRS[$i]);
        print('    ' . $DIRS[$i] . "\r\n");
    }
}

print("\r\n" . "\r\n");

foreach($commands as $cmd) {
    print(">>>> " . $cmd . "\r\n" . "\r\n");
    shell_exec($cmd);
}

print("\r\n" . "\r\n" . "- - - - Done! - - - -" . "\r\n" . "\r\n");