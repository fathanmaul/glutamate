<?php

declare(strict_types=1);

namespace Glutamate\PHPStan;

use Carbon\Carbon;
use Glutamate\Columns\BoolColumn;
use Glutamate\Columns\Column;
use Glutamate\Columns\DateTimeColumn;
use Glutamate\Columns\DecimalColumn;
use Glutamate\Columns\EnumColumn;
use Glutamate\Columns\ForeignIdColumn;
use Glutamate\Columns\IdColumn;
use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Glutamate\Columns\TextColumn;
use Glutamate\Columns\TimestampsColumn;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node>
 */
final class EloquentWhereTypeRule implements Rule
{
    public function __construct(private ReflectionProvider $reflectionProvider) {}

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof MethodCall && ! $node instanceof Node\Expr\StaticCall) {
            return [];
        }

        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        $methodName = $node->name->name;

        if (! in_array($methodName, ['where', 'orWhere', 'whereIn', 'orWhereIn'], true)) {
            return [];
        }

        if ($node instanceof MethodCall) {
            $varType = $scope->getType($node->var);
            $isBuilder = (new ObjectType('Illuminate\Database\Eloquent\Builder'))->isSuperTypeOf($varType)->yes() ||
                         (new ObjectType('Illuminate\Database\Query\Builder'))->isSuperTypeOf($varType)->yes() ||
                         (new ObjectType('Illuminate\Database\Eloquent\Relations\Relation'))->isSuperTypeOf($varType)->yes();

            if (! $isBuilder) {
                return [];
            }
        } else {
            if (! $node->class instanceof Node\Name) {
                return [];
            }
            $className = $scope->resolveName($node->class);

            if (! $this->reflectionProvider->hasClass($className)) {
                return [];
            }

            $classRef = $this->reflectionProvider->getClass($className);

            if (! $classRef->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                return [];
            }
        }

        $args = $node->getArgs();

        if (count($args) < 2) {
            return [];
        }

        $firstArgType = $scope->getType($args[0]->value);

        $columnObjectType = new ObjectType(Column::class);

        if (! $columnObjectType->isSuperTypeOf($firstArgType)->yes()) {
            return [];
        }

        // Try to get the generic template type T from Column<T>
        $referencedType = $firstArgType->getTemplateType(Column::class, 'T');

        if ($referencedType instanceof ErrorType || $referencedType instanceof MixedType) {
            // Fallback: resolve from class name
            $referencedType = $this->resolveTypeFromColumnClass($firstArgType);
        }

        if ($referencedType instanceof MixedType) {
            return [];
        }

        // For where/orWhere: if 2 args -> value is args[1]; if 3 args (operator) -> value is args[2]
        $valueArg = count($args) === 2 ? $args[1] : $args[2];
        $valueType = $scope->getType($valueArg->value);

        $isWhereIn = str_contains($methodName, 'In');

        if ($isWhereIn) {
            $iterableValueType = $valueType->getIterableValueType();

            if (! $referencedType->accepts($iterableValueType, $scope->isDeclareStrictTypes())->yes()) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        'Parameter #2 of query method %s() expects array of %s, array of %s given.',
                        $methodName,
                        $referencedType->describe(VerbosityLevel::typeOnly()),
                        $iterableValueType->describe(VerbosityLevel::typeOnly()),
                    ))->identifier('glutamate.whereType')->build(),
                ];
            }
        } else {
            if (! $referencedType->accepts($valueType, $scope->isDeclareStrictTypes())->yes()) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        'Parameter #%d of query method %s() expects %s, %s given.',
                        count($args) === 2 ? 2 : 3,
                        $methodName,
                        $referencedType->describe(VerbosityLevel::typeOnly()),
                        $valueType->describe(VerbosityLevel::typeOnly()),
                    ))->identifier('glutamate.whereType')->build(),
                ];
            }
        }

        return [];
    }

    private function resolveTypeFromColumnClass(Type $columnType): Type
    {
        $classNames = $columnType->getObjectClassNames();

        if (empty($classNames)) {
            return new MixedType;
        }

        $className = $classNames[0];

        return match ($className) {
            IntColumn::class => new IntegerType,
            BoolColumn::class => new BooleanType,
            StringColumn::class,
            TextColumn::class,
            EnumColumn::class,
            DecimalColumn::class => new StringType,
            DateTimeColumn::class => new ObjectType(Carbon::class),
            ForeignIdColumn::class => new IntegerType,
            IdColumn::class => new IntegerType,
            TimestampsColumn::class => new MixedType,
            default => new MixedType,
        };
    }
}
