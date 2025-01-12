<?php

/**
 * expression-types.php.
 *
 *
 * Copyright (c) 2010-2013, Justin Swanhart
 * with contributions by André Rothe <arothe@phosco.info, phosco@gmx.de>
 * and David Négrier <d.negrier@thecodingmachine.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 */
namespace SQLParser\Node;

use Doctrine\DBAL\Connection;
use Mouf\MoufInstanceDescriptor;
use Mouf\MoufManager;
use SQLParser\Node\Traverser\VisitorInterface;

/**
 * This class represents a node that is an SQL expression.
 *
 * @author David Négrier <d.negrier@thecodingmachine.com>
 */
class Reserved implements NodeInterface
{
    private $baseExpression;

    /**
     * Returns the base expression (the string that generated this expression).
     *
     * @return string
     */
    public function getBaseExpression()
    {
        return $this->baseExpression;
    }

    /**
     * Sets the base expression (the string that generated this expression).
     *
     * @param string $baseExpression
     */
    public function setBaseExpression($baseExpression)
    {
        $this->baseExpression = $baseExpression;
    }

    private $brackets = false;

    /**
     * Returns true if the expression is between brackets.
     *
     * @return bool
     */
    public function hasBrackets()
    {
        return $this->brackets;
    }

    /**
     * Sets to true if the expression is between brackets.
     *
     * @Important
     *
     * @param bool $brackets
     */
    public function setBrackets($brackets)
    {
        $this->brackets = $brackets;
    }

    /**
     * Returns a Mouf instance descriptor describing this object.
     *
     * @param MoufManager $moufManager
     *
     * @return MoufInstanceDescriptor
     */
    public function toInstanceDescriptor(MoufManager $moufManager)
    {
        $instanceDescriptor = $moufManager->createInstance(get_called_class());
        $instanceDescriptor->getProperty('baseExpression')->setValue(NodeFactory::nodeToInstanceDescriptor($this->baseExpression, $moufManager));
        $instanceDescriptor->getProperty('brackets')->setValue(NodeFactory::nodeToInstanceDescriptor($this->brackets, $moufManager));

        return $instanceDescriptor;
    }

    /**
     * Renders the object as a SQL string.
     *
     * @param array      $parameters
     * @param Connection $dbConnection
     * @param int|number $indent
     * @param int        $conditionsMode
     *
     * @return string
     */
    public function toSql(array $parameters = array(), Connection $dbConnection = null, $indent = 0, $conditionsMode = self::CONDITION_APPLY, bool $extrapolateParameters = true)
    {
        $sql = '';

        if ($this->baseExpression) {
            $sql .= ' '.$this->baseExpression.' ';
        }

        if ($this->brackets) {
            $sql = '('.$sql.')';
        }

        return $sql;
    }

    /**
     * Walks the tree of nodes, calling the visitor passed in parameter.
     *
     * @param VisitorInterface $visitor
     */
    public function walk(VisitorInterface $visitor)
    {
        $node = $this;
        $result = $visitor->enterNode($node);
        if ($result instanceof NodeInterface) {
            $node = $result;
        }

        return $visitor->leaveNode($node);
    }
}
