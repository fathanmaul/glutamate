<?php

declare(strict_types=1);

namespace Glutamate\Schema;

use Glutamate\Columns\Column;
use ReflectionClass;

final class DocblockGenerator
{
    /**
     * @param  class-string  $modelClass
     * @param  array<string, Column<mixed>>  $columns
     */
    public static function update(string $modelClass, array $columns): void
    {
        $ref = new ReflectionClass($modelClass);
        $filePath = $ref->getFileName();

        if (! $filePath || ! file_exists($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return;
        }

        $docComment = $ref->getDocComment();

        $propertyLines = [];
        foreach ($columns as $name => $column) {
            $phpType = $column->phpType();
            $propertyLines[] = " * @property {$phpType} \${$name}";
        }

        if ($docComment !== false) {
            $lines = preg_split('/\r\n|\r|\n/', $docComment);

            if ($lines === false) {
                return;
            }
            $newLines = [];
            foreach ($lines as $line) {
                if (str_contains($line, '@property')) {
                    continue;
                }

                if (trim($line) === '*/') {
                    foreach ($propertyLines as $propLine) {
                        $newLines[] = $propLine;
                    }
                }
                $newLines[] = $line;
            }
            $newDocComment = implode("\n", $newLines);
            $content = str_replace($docComment, $newDocComment, $content);
        } else {
            $newDocLines = ['/**'];
            foreach ($propertyLines as $propLine) {
                $newDocLines[] = $propLine;
            }
            $newDocLines[] = ' */';

            $lines = preg_split('/\r\n|\r|\n/', $content);

            if ($lines === false) {
                return;
            }

            $startLineIndex = $ref->getStartLine() - 1;
            $insertIndex = $startLineIndex;

            while ($insertIndex > 0) {
                $prevLine = trim($lines[$insertIndex - 1]);

                if (str_starts_with($prevLine, '#[')) {
                    $insertIndex--;
                } else {
                    break;
                }
            }

            array_splice($lines, $insertIndex, 0, $newDocLines);
            $content = implode("\n", $lines);
        }

        file_put_contents($filePath, $content);
    }
}
