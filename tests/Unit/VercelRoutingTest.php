<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

class VercelRoutingTest extends TestCase
{
    private function resolve(string $path): array
    {
        $config=json_decode(file_get_contents(dirname(__DIR__,2).'/vercel.json'),true,512,JSON_THROW_ON_ERROR);
        foreach ($config['routes'] as $route) {
            if (($route['handle']??null)==='filesystem') {
                if ($path==='/') return ['static','/index.php'];
                if (is_file(dirname(__DIR__,2).'/public'.$path)) return ['static',$path];
                continue;
            }
            if (!preg_match('~^'.$route['src'].'$~D',$path,$matches)) continue;
            if (isset($route['status'])) return ['denied',$route['status']];
            $dest=preg_replace_callback('/\$(\d+)/',fn($m)=>$matches[(int)$m[1]]??'', $route['dest']);
            return [$dest==='/api/index.php'?'runtime':'static',$dest];
        }
        return ['unmatched',null];
    }
    /** @dataProvider requests */
    public function test_route_decisions(string $path,string $kind,$target): void
    {
        $this->assertSame([$kind,$target],$this->resolve($path),$path);
    }
    public static function requests(): array
    {
        $cases=[];
        foreach(['/','/login','/register','/profil','/dashboard','/admin','/verifikasi-spmb/token','/api/unknown'] as $path) $cases[]=[$path,'runtime','/api/index.php'];
        foreach(['/build/assets/app-test.js','/build/assets/app-test.css','/build/manifest.json','/images/logo-miru.png','/images/nested/photo.webp','/favicon.ico','/robots.txt','/sitemap.xml','/site.webmanifest'] as $path)$cases[]=[$path,'static',$path];
        foreach(['/index.php','/api/index.php','/public/index.php','/images/test.php','/build/test.PHP','/images/test.php.jpg','/file.phtml','/file.phar','/index.php/extra','/.env','/.env.supabase','/.git/config','/images/.env','/build/.git/config','/storage','/storage/spmb/private.pdf'] as $path)$cases[]=[$path,'denied',404];
        return $cases;
    }
    public function test_no_filesystem_phase_and_root_has_explicit_runtime_rule(): void
    {
        $config=json_decode(file_get_contents(dirname(__DIR__,2).'/vercel.json'),true,512,JSON_THROW_ON_ERROR);
        $this->assertSame('public',$config['outputDirectory']);
        $this->assertSame('vercel-php@0.6.2',$config['functions']['api/index.php']['runtime']);
        $this->assertSame([],array_values(array_filter($config['routes'],fn($r)=>isset($r['handle']))));
        $roots=array_values(array_filter($config['routes'],fn($r)=>($r['src']??null)==='/'));
        $this->assertCount(1,$roots);$this->assertSame('/api/index.php',$roots[0]['dest']);
    }
    public function test_public_php_is_excluded_from_uploaded_static_artifacts(): void
    {
        $ignore=file_get_contents(dirname(__DIR__,2).'/.vercelignore');
        $this->assertStringContainsString('/public/index.php',$ignore);
        $this->assertStringNotContainsString('/api/index.php',$ignore);
    }
}