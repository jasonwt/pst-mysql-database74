<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Pst\Core\Types\Type;

final class SourceCode {
    private static function stringIndent(int $indentLevel): string {
        if ($indentLevel < 0) {
            throw new \Exception("Invalid indent level: $indentLevel");
        }

        return str_repeat("    ", $indentLevel);
    }

    private static function generate($value, ?string $name, int $indentLevel): string {
        $valueType = str_replace(["boolean", "integer", "double"], ["bool", "int", "float"], gettype($value));

        if ($valueType === "NULL") {
            $value = "null";
        } else if (in_array($valueType, ["bool", "int", "float", "string"])) {
            $value = is_string($value) ? "'" . $value . "'" : $value;
        } else if ($valueType === "array") {
            $value = "[" . implode(", ", array_map(function($key, $value) use ($indentLevel) {
                return self::generate($key, null, 0) . " => " . self::generate($value, null, 0);
            }, array_keys($value), $value)) . "]";
        } else {
            throw new \Exception("Invalid value type: $valueType");
        }

        $value = $name === null ? $value : $valueType . " " . $name . " = " . $value . ";";

        return self::stringIndent($indentLevel) . $value;
    }

    public static function fromValue($value, ?string $name = null): string {
        return self::generate($value, $name, 0) . ";\n";
    }

    
    
}

//$value = (int) 1;

echo "\nvalue = " . print_r($value, true) . "\n\n";

echo SourceCode::fromValue($value);

print_r(['value1' => [0 => 'zero', 1 => 'one', 2 => 'two', 'three' => 'three'], 'value2' => [0 => 'four', 5 => 'five', 6 => 'six', 'seven' => 'seven']]);