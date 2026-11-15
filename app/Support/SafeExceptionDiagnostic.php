<?php
namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Throwable;

final class SafeExceptionDiagnostic
{
    public static function write(Throwable $exception, ?Request $request = null): void
    {
        try {
            $root=$exception;
            for($depth=0;$depth<8 && $root->getPrevious();$depth++) $root=$root->getPrevious();
            $message=self::message($root);
            $data=[
                'exception_class'=>self::className($root),
                'message'=>$message,
                'message_redacted'=>$message!==$root->getMessage(),
                'file'=>self::file($root->getFile()),
                'line'=>$root->getLine(),
                'request_path'=>self::path($request),
            ];
            if($root!==$exception) $data['wrapper_class']=self::className($exception);
            // Mask environment/config secrets even if they collide with technical metadata.
            foreach(self::secrets() as $secret) foreach($data as $key=>$value) {
                if(is_string($value)) {
                    $data[$key]=str_replace($secret,'[redacted]',$value);
                    if($key==='message' && $data[$key]!==$value) $data['message_redacted']=true;
                }
            }
            error_log('[laravel.exception] '.json_encode($data,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE));
        } catch(Throwable $loggingFailure) {
            // No original message/trace is used even when diagnostics itself fails.
            try { error_log('[laravel.exception] {"message":"Safe diagnostic unavailable"}'); } catch(Throwable $ignored) { /* Rendering the original exception must still proceed. */ }
        }
    }

    private static function message(Throwable $e): string
    {
        $raw=$e->getMessage();
        // QueryException messages may embed SQL, bindings, passwords and personal data.
        if(preg_match('/SQLSTATE\[([A-Z0-9]{5})\]/',$raw,$match)) {
            $category=['42P01'=>'relation does not exist','42703'=>'column does not exist','28P01'=>'authentication failed','08006'=>'connection failure','23505'=>'unique constraint violation','23502'=>'not-null constraint violation'][$match[1]] ?? 'database error';
            return 'SQLSTATE['.$match[1].']: '.$category.' (SQL and values omitted)';
        }
        // Reconstruct technical messages; never copy arbitrary identifiers/quoted values.
        $patterns=[
            '/^Undefined variable\b/'=>'Undefined variable (name omitted)',
            '/^Undefined array key\b/'=>'Undefined array key (key omitted)',
            '/^Undefined property\b/'=>'Undefined property (details omitted)',
            '/^Call to a member function .* on null/'=>'Call to a member function on null (method omitted)',
            '/^Call to undefined (?:function|method)\b/'=>'Call to undefined function or method (details omitted)',
            '/^Class .* not found/'=>'Class not found (name omitted)',
            '/^Target class .* does not exist/'=>'Target class does not exist (name omitted)',
            '/^View .* not found/'=>'View not found (name omitted)',
            '/^Vite manifest not found/'=>'Vite manifest not found (path omitted)',
            '/^Unable to locate file in Vite manifest/'=>'Unable to locate file in Vite manifest (path omitted)',
            '/^could not find driver$/i'=>'could not find driver',
            '/^Too few arguments to function\b/'=>'Too few arguments to function (details omitted)',
            '/^Allowed memory size of \d+ bytes exhausted/'=>'Allowed memory size exhausted (details omitted)',
            '/^Maximum execution time of \d+ seconds exceeded/'=>'Maximum execution time exceeded (details omitted)',
        ];
        foreach($patterns as $pattern=>$safe) if(preg_match($pattern,$raw)) return $safe;
        if($e instanceof \TypeError) return 'Type mismatch (values and details omitted)';
        return 'Unstructured exception message withheld for privacy';
    }

    private static function className(Throwable $e): string
    {
        $name=get_class($e);
        return str_contains($name,'@anonymous') ? 'AnonymousException' : substr($name,0,180);
    }

    private static function file(string $file): string
    {
        $file=str_replace('\\','/',$file);$root=str_replace('\\','/',dirname(__DIR__,2)).'/';
        if(str_starts_with($file,$root)) return substr($file,strlen($root),200);
        if(($at=strpos($file,'/framework/views/'))!==false) return 'compiled-view/'.basename($file);
        return '[external]/'.substr(basename($file),0,160);
    }

    private static function path(?Request $request): string
    {
        $route=$request?->route();
        if($route instanceof Route) return substr('/'.ltrim($route->uri(),'/'),0,200);
        $path=$request?->getPathInfo();
        return in_array($path,['/','/login','/register'],true) ? $path : '[unmatched route]';
    }

    private static function secrets(): array
    {
        $values=[];
        foreach(array_merge(array_values(getenv() ?: []),array_values($_ENV)) as $value) if(is_string($value) && strlen($value)>=4) $values[]=$value;
        if(function_exists('app') && app()->bound('config')) {
            $config=app('config');
            foreach(['app.key','database.connections','database.redis','filesystems.disks','mail.mailers'] as $key) {
                $value=$config->get($key);
                if(is_string($value) && $value!=='') $values[]=$value;
                if(is_array($value)) array_walk_recursive($value,function($v,$k)use(&$values){
                    if(is_string($v) && $v!=='' && preg_match('/password|secret|token|^key$/i',(string)$k))$values[]=$v;
                });
            }
        }
        $values=array_values(array_unique($values));usort($values,fn($a,$b)=>strlen($b)<=>strlen($a));return $values;
    }
}