<?php

namespace SQLParser\Node;

use Doctrine\DBAL\Connection;

/**
 * This class represents a set of ... WHEN ... THEN ... construct (inside a CASE).
 *
 * @author David Négrier <d.negrier@thecodingmachine.com>
 */
class WhenConditions extends AbstractManyInstancesOperator
{
    private $value;

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value.
     *
     * @Important
     *
     * @param NodeInterface|NodeInterface[]|string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Renders the object as a SQL string.
     *
     * @param Connection $dbConnection
     * @param array      $parameters
     * @param number     $indent
     * @param int        $conditionsMode
     *
     * @return string
     */
    public function toSql(array $parameters = array(), Connection $dbConnection = null, $indent = 0, $conditionsMode = self::CONDITION_APPLY, bool $extrapolateParameters = true)
    {
        $fullSql = '';

        if ($this->value) {
            $fullSql = NodeFactory::toSql($this->value, $dbConnection, $parameters, ' ', false, $indent, $conditionsMode, $extrapolateParameters);
        }

        foreach ($this->getOperands() as $operand) {
            $sql = NodeFactory::toSql($operand, $dbConnection, $parameters, ' ', false, $indent, $conditionsMode, $extrapolateParameters);
            if ($sql != null) {
                $fullSql .= "\n".str_repeat(' ', $indent).'WHEN '.$sql;
            }
        }

        return $fullSql;
    }

    /**
     * Returns the symbol for this operator.
     *
     * @return string
     */
    protected function getOperatorSymbol()
    {
        return 'WHEN';
    }
}
