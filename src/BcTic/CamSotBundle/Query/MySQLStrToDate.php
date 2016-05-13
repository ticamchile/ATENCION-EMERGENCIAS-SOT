<?php

namespace BcTic\CamSotBundle\Query;

use \Doctrine\ORM\Query\AST\Functions\FunctionNode;
use \Doctrine\ORM\Query\Lexer;

class MySQLStrToDate extends FunctionNode
{
    private $stringExpression;
    private $incomingExpression;
    private $outgoingExpression;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return ' DATE_FORMAT(
                   STR_TO_DATE(
                      ' . $sqlWalker->walkSimpleArithmeticExpression($this->stringExpression) . ',  
                      ' . $sqlWalker->walkSimpleArithmeticExpression($this->incomingExpression) . '),
                 ' . $sqlWalker->walkSimpleArithmeticExpression($this->outgoingExpression) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {

        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->stringExpression = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->incomingExpression = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->outgoingExpression = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)

    }
}