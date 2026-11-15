<?php
// Executed by the community runtime's Composer "vercel" hook, not by local install.
// Its upstream installer ignores platform requirements; re-check explicitly.
// Safe diagnostics run BEFORE Composer platform checks, which can abort the hook.
// Do not load .env or print environment values here.
if (in_array('--diagnostics', $argv ?? [], true)) {
    fwrite(STDOUT, 'PHP_VERSION='.PHP_VERSION."\n");
    fwrite(STDOUT, 'PHP_BINARY='.PHP_BINARY."\n");
    fwrite(STDOUT, 'PHP_INI='.(php_ini_loaded_file() ?: '(none)')."\n");
    fwrite(STDOUT, 'PHP_INI_SCANNED='.(php_ini_scanned_files() ?: '(none)')."\n");
    foreach (['pdo_pgsql','xmlwriter','mbstring','fileinfo','dom','openssl'] as $extension) {
        fwrite(STDOUT, 'ext-'.$extension.'='.(extension_loaded($extension) ? 'loaded' : 'MISSING')."\n");
    }
    exit(0); // Diagnostics only. The mandatory checks below still run separately.
}
$required=['pdo_pgsql','xmlwriter','mbstring','fileinfo','dom','openssl'];
foreach($required as $extension) {
    if(!extension_loaded($extension)) {
        fwrite(STDERR,"Required Vercel PHP extension unavailable: ".$extension."\n");
        exit(1);
    }
}
if(PHP_VERSION_ID<80200 || PHP_VERSION_ID>=80300) {
    fwrite(STDERR,"This deployment pins the PHP 8.2 runtime. Revalidate before changing it.\n");
    exit(1);
}

$requiredEnv=['APP_KEY','APP_URL','DB_HOST','DB_DATABASE','DB_USERNAME','DB_PASSWORD','AWS_ACCESS_KEY_ID','AWS_SECRET_ACCESS_KEY','AWS_DEFAULT_REGION','AWS_ENDPOINT','AWS_BUCKET','AWS_PRIVATE_BUCKET','AWS_URL'];
foreach($requiredEnv as $name) {
    if(trim((string)getenv($name))==='') {
        fwrite(STDERR,"Fill the real Vercel environment variable before deploy: ".$name."\n");
        exit(1);
    }
}
foreach(['APP_URL','AWS_ENDPOINT','AWS_URL'] as $name) {
    $value=(string)getenv($name);
    if(parse_url($value,PHP_URL_SCHEME)!=='https' || in_array(parse_url($value,PHP_URL_HOST),['localhost','127.0.0.1'],true)) {
        fwrite(STDERR,"Production HTTPS URL required: ".$name."\n");
        exit(1);
    }
}
if(getenv('AWS_BUCKET')===getenv('AWS_PRIVATE_BUCKET')) {
    fwrite(STDERR,"Use separate public media and private SPMB buckets.\n");
    exit(1);
}

foreach(['APP_ENV'=>'production','DB_CONNECTION'=>'pgsql','SESSION_DRIVER'=>'database','CACHE_DRIVER'=>'database','PUBLIC_FILESYSTEM_DRIVER'=>'s3','SPMB_FILESYSTEM_DRIVER'=>'s3'] as $name=>$expected) {
    if(getenv($name)!==$expected) {
        fwrite(STDERR,"Vercel requires ".$name."=".$expected."\n");
        exit(1);
    }
}
if(filter_var(getenv('APP_DEBUG'),FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR,"APP_DEBUG must be false for this deployment.\n");
    exit(1);
}
if(!in_array(getenv('DB_SSLMODE'),['require','verify-ca','verify-full'],true)) {
    fwrite(STDERR,"Use SSL for the Supabase SQL connection.\n");
    exit(1);
}
if(getenv('DB_PORT')==='6543' && !filter_var(getenv('DB_EMULATE_PREPARES'),FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR,"Supabase transaction pooler requires DB_EMULATE_PREPARES=true.\n");
    exit(1);
}
$root=dirname(__DIR__);
if(!is_file($root.'/public/build/manifest.json')) {
    fwrite(STDERR,"Vite manifest missing. Run npm run build before packaging the function.\n");
    exit(1);
}
fwrite(STDOUT,"PHP 8.2 extensions and Vite manifest verified. No database migration was run.\n");
