<?php

require __DIR__ . '/parsedown/Parsedown.php';
require __DIR__ . '/parsedown-extra/ParsedownExtra.php';

class MathsParsedown extends ParsedownExtra {
	public function __construct () {
		$this->InlineTypes['$'] = array('Maths');
		$this->inlineMarkerList .= '$';

		$this->BlockTypes['$']  = array('Maths');

		$this->InlineTypes['-'] = array('Hyphenator');
		$this->inlineMarkerList .= '-';

		$this->setMarkupEscaped(FALSE);
		
		if (is_callable('parent::__construct')) {
			parent::__construct();
		}
	}

	public function inlineMaths ($Excerpt) {
		$marker = preg_quote($Excerpt['text'][0], '/');
		if (preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s', $Excerpt['text'], $matches))
		{
			$text = $matches[2];
			$text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
			//$text = preg_replace("/[ ]*\n/", ' ', $text);
			return array(
				'extent' => strlen($matches[0]),
				'element' => array(
					'name' => 'span',
					'attributes' => array(
						'class' => 'math'.(strlen($matches[1]) > 1 ? ' display' : '')
					),
					'text' => $text,
				),
			);
		}
	}

	protected function blockMaths($Line, $Block = null)
	{
		if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*$/', $Line['text'], $matches))
		{
			$Block = array(
				'char' => $Line['text'][0],
				'element' => array(
					'name' => 'div',
					'handler' => 'element',
					'text' => $Element,
				),
			);
			return $Block;
		}
	}

	protected function blockMathsContinue($Line, $Block)
	{
		if (isset($Block['complete']))
		{
			return;
		}
		if (isset($Block['interrupted']))
		{
			$Block['element']['text']['text'] .= "\n";
			unset($Block['interrupted']);
		}
		if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text']))
		{
			$Block['element']['text']['text'] = substr($Block['element']['text']['text'], 1);
			$Block['complete'] = true;
			return $Block;
		}
		$Block['element']['text']['text'] .= "\n".$Line['body'];;
		return $Block;
	}
	protected function blockMathsComplete($Block)
	{
		$text = $Block['element']['text']['text'];
		$text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
		$Block['element']['text']['text'] = $text;
		return $Block;
	}

	protected function blockFencedCode($Line)
	{
		if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([\w-]+)?(:(\d+)?)?[ ]*$/', $Line['text'], $matches))
		{
			$Element = array(
				'name' => 'code',
				'text' => '',
			);

			$class = [];

			if (isset($matches[1]))
			{
				$class []= 'prettyprint lang-'.$matches[1];
			}

			if (isset($matches[2])) {
				$class []= "linenums" . (isset($matches[3]) ? $matches[2] : "");
			}

			$Element['attributes'] = array(
				'class' => implode(" ", $class),
			);

			$Block = array(
				'char' => $Line['text'][0],
				'element' => array(
					'name' => 'pre',
					'handler' => 'element',
					'text' => $Element,
				),
			);

			return $Block;
		}
	}

	protected function inlineHyphenator ($Excerpt) {
		if (isset($Excerpt['text'][1]) and $Excerpt['text'][1] === '-') {
			return array(
				'markup' => '—',
				'extent' => 2,
			);
		}
	}

	/**
	 * Allow resuming numbering from non-1
	 */
	protected function blockList($Line) {
		$return = parent::blockList($Line);
		if (isset($return['element']['attributes']['start'])) {
			$return['element']['attributes']['style'] = 'counter-reset: ol ' . $return['element']['attributes']['start'];
		}
		return $return;
	}

}
