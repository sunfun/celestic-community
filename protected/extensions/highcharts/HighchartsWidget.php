<?php

/**
 * HighchartsWidget class file.
 *
 * @author Milo Schuman <miloschuman@gmail.com>
 * @link http://yii-highcharts.googlecode.com/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version 0.2
 */

/**
 * HighchartsWidget encapsulates the {@link http://www.highcharts.com/ Highcharts}
 * charting library's Chart object.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->Widget('ext.highcharts.HighchartsWidget', array(
 *    'options'=>array(
 *       'title' => array('text' => 'Fruit Consumption'),
 *       'xAxis' => array(
 *          'categories' => array('Apples', 'Bananas', 'Oranges')
 *       ),
 *       'yAxis' => array(
 *          'title' => array('text' => 'Fruit eaten')
 *       ),
 *       'series' => array(
 *          array('name' => 'Jane', 'data' => array(1, 0, 4)),
 *          array('name' => 'John', 'data' => array(5, 7, 3))
 *       )
 *    )
 * ));
 * </pre>
 *
 * By configuring the {@link $options} property, you may specify the options
 * that need to be passed to the Highcharts JavaScript object. Please refer to
 * the demo gallery and documentation on the {@link http://www.highcharts.com/
 * Highcharts website} for possible options.
 * 
 * Alternatively, you can use a valid JSON string in place of an associative
 * array to specify options:
 *
 * <pre>
 * $this->Widget('ext.highcharts.HighchartsWidget', array(
 *    'options'=>'{
 *       "title": { "text": "Fruit Consumption" },
 *       "xAxis": {
 *          "categories": ["Apples", "Bananas", "Oranges"]
 *       },
 *       "yAxis": {
 *          "title": { "text": "Fruit eaten" }
 *       },
 *       "series": [
 *          { "name": "Jane", "data": [1, 0, 4] },
 *          { "name": "John", "data": [5, 7,3] }
 *       ]
 *    }'
 * ));
 * </pre>
 *
 * Note: You must provide a valid JSON string (e.g. double quotes) when using
 * the second option. You can quickly validate your JSON string online using
 * {@link http://jsonlint.com/ JSONLint}.
 *
 * Note: You do not need to specify the <code>chart->renderTo</code> option as
 * is shown in many of the examples on the Highcharts website. This value is
 * automatically populated with the id of the widget's container element. If you
 * wish to use a different container, feel free to specify a custom value.
 */
class HighchartsWidget extends CWidget {

	public $options = array();
	public $htmlOptions = array();

	/**
	 * Renders the widget.
	 */
	public function run() {
		$id = $this->getId();
		$this->htmlOptions['id'] = $id;

		echo CHtml::openTag('div', $this->htmlOptions);
		echo CHtml::closeTag('div');

		// check if options parameter is a json string
		if (is_string($this->options)) {
			if (!$this->options = CJSON::decode($this->options))
				throw new CException('The options parameter is not valid JSON.');
			// TODO translate exception message
		}

		// merge options with default values
		$defaultOptions = array('chart' => array('renderTo' => $id), 'exporting' => array('enabled' => true));
		//$this->options = array_replace_recursive($defaultOptions, $this->options);
		
		if (function_exists('array_replace_recursive'))
		{
			$this->options = array_replace_recursive($defaultOptions, $this->options);
		}
		else
			$this->options = HighchartsWidget::array_join($defaultOptions, $this->options);

		$jsOptions = CJavaScript::encode($this->options);
		$this->registerScripts(__CLASS__ . '#' . $id, "var chart = new Highcharts.Chart($jsOptions);");
	}
	
	private function array_join()
	{
		// Get array arguments
		$arrays = func_get_args();

		// Define the original array
		$original = array_shift($arrays);

		// Loop through arrays
		foreach ($arrays as $array)
		{
			// Loop through array key/value pairs
			foreach ($array as $key => $value)
			{
				// Value is an array
				if (is_array($value))
				{
					// Traverse the array; replace or add result to original array
					$original[$key] = HighchartsWidget::array_join($original[$key], $array[$key]);
				}

				// Value is not an array
				else
				{
					// Replace or add current value to original array
					$original[$key] = $value;
				}
			}
		}

		// Return the joined array
		return $original;
	} 

	/**
	 * Publishes and registers the necessary script files.
	 *
	 * @param string the id of the script to be inserted into the page
	 * @param string the embedded script to be inserted into the page
	 */
	protected function registerScripts($id, $embeddedScript) {
		$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
		$baseUrl = Yii::app()->getAssetManager()->publish($basePath, false, 1, true);
		$scriptFile = YII_DEBUG ? '/highcharts.src.js' : '/highcharts.js';

		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile($baseUrl . $scriptFile);

		// register exporting module if enabled via the 'exporting' option
		if ($this->options['exporting']['enabled']) {
			$scriptFile = YII_DEBUG ? '/exporting.src.js' : '/exporting.js';
			$cs->registerScriptFile($baseUrl . $scriptFile);
		}

		$cs->registerScript($id, $embeddedScript);
	}

}

?>