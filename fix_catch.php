<?php

$dir = __DIR__.'/app/Http/Controllers';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());

        $search = <<<EOT
        } catch (\Throwable \$e) {
            report(\$e);
EOT;

        $replace = <<<EOT
        } catch (\Illuminate\Validation\ValidationException \$e) {
            throw \$e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException \$e) {
            throw \$e;
        } catch (\Throwable \$e) {
            report(\$e);
EOT;

        if (strpos($content, $search) !== false) {
            $newContent = str_replace($search, $replace, $content);
            file_put_contents($file->getPathname(), $newContent);
            echo 'Updated: '.$file->getPathname()."\n";
        } else {
            // Check for indented version
            $search2 = <<<EOT
    } catch (\Throwable \$e) {
        report(\$e);
EOT;
            $replace2 = <<<EOT
    } catch (\Illuminate\Validation\ValidationException \$e) {
        throw \$e;
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException \$e) {
        throw \$e;
    } catch (\Throwable \$e) {
        report(\$e);
EOT;
            if (strpos($content, $search2) !== false) {
                $newContent = str_replace($search2, $replace2, $content);
                file_put_contents($file->getPathname(), $newContent);
                echo 'Updated: '.$file->getPathname()."\n";
            }
        }
    }
}
echo "Done.\n";
