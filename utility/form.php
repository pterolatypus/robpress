<?php
class Form {

	public function __construct($xssHelper) {
		$this->XSS = $xssHelper;
	}

	public function start($options=array()) {
		$action = isset($options['action']) ? $options['action'] : '';
		$enctype = (isset($options['type']) && $options['type'] == 'file') ? 'enctype="multipart/form-data"' : ''; //Handle file uploads
		return '<form role="form" method="post" action="'.$action.'" '.$enctype.'>';
	}

	public function file($options) {
		return '<input type="file" class="form-control" id="' . $options['field'] . '" name="' . $options['field'] . '" placeholder="' . $options['placeholder'] . '" value="' . $this->XSS->sanitise($options['value'], array('html')) . '">';
	}

	public function checkbox($options) {
		$checked = (isset($options['value']) && !empty($options['value'])) ? 'checked="checked"' : '';
			$output = '
				<div class="checkbox">
				<label>
				<input type="checkbox" name="'.$options['field'].'" '.$checked.' value="1">'.$this->XSS->sanitise($options['label'], array('html')).'
				</label>
				</div>';
		return $output;
	}

	public function select($options) {
		$output = '<select class="form-control" id="' . $options['field'] . '" name="' . $options['field'] . '">';
		foreach($options['items'] as $value=>$label) {
			$checked = ($options['value'] == $value) ? 'selected="selected"' : '';
			$output .= '<option value="'. $value .'" '.$checked.'>'.$label.'</option>';
		}
		$output .= '</select>';
		return $output;
	}


	public function checkboxes($options) {
		$output = '';
		foreach($options['items'] as $value=>$label) {
			$checked = (is_array($options['value']) && in_array($value,$options['value'])) ? 'checked="checked"' : '';
			$output .= '
				<div class="checkbox">
				<label>
				<input type="checkbox" name="'.$options['field'].'[]" '.$checked.' value="'. $this->XSS->sanitise($value, array('html')) .'">'.$this->XSS->sanitise($label, array('html')).'
				</label>
				</div>';
		}
		return $output;
	}

	public function hidden($options) {
		return '<input type="hidden" id="' . $options['field'] . '" name="' . $options['field'] . '" value="' . $this->XSS->sanitise($options['value'], array('html')) . '">';
	}

	public function text($options) {
		return '<input type="text" class="form-control" id="' . $options['field'] . '" name="' . $options['field'] . '" placeholder="' . $options['placeholder'] . '" value="' . $this->XSS->sanitise($options['value'], array('html')) . '">';
	}

	public function datetime($options) {
		return '<input type="text" class="datetime form-control" id="' . $options['field'] . '" name="' . $options['field'] . '" placeholder="' . $options['placeholder'] . '" value="' . $this->XSS->sanitise($options['value'], array('html')) . '">';
	}

	public function textarea($options) {
		return '<textarea style="height: 200px" class="form-control" id="' . $options['field'] . '" name="' . $options['field'] . '">' . $this->XSS->sanitise($options['value'], array('html')) . '</textarea>';
	}

	public function wysiwyg($options) {
		$f3 = Base::instance();
		$base = $f3->get('site.base');
		return '<textarea style="height: 200px" class="wysiwyg form-control" id="' . $options['field'] . '" name="' . $options['field'] . '">' . $this->XSS->sanitise($options['value'], array('html')) . '</textarea>
		<script type="text/javascript">CKEDITOR.replace(\'' . $options['field'] . "', {
toolbarGroups: [
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'colors' },
		{ name: 'links' },
		{ name: 'insert' },
	],
	filebrowserUploadUrl: '$base/lib/upload.php'
}


);</script>
		";
	}


	public function password($options) {
		return '<input type="password" class="form-control" id="' . $options['field'] . '" name="' . $options['field'] . '" placeholder="' . $options['placeholder'] . '" value="">';
	}

	public function submit($options) {
		if(!isset($options['class'])) { $options['class'] = 'btn-primary'; }
		return '<input type="submit" class="btn '.$options['class'].'" id="' . $options['field'] . '" name="' . $options['field'] . '" value="' . $this->XSS->sanitise($options['label'], array('html')) . '">';
	}

	public function end() {
		return '</form>';
	}

	public function add($field,$options=array()) {
		$options['label'] = $label = isset($options['label']) ? $options['label'] : ucfirst($field);
		$type = isset($options['type']) ? $options['type'] : 'text';
		if(isset($options['value'])) {
			$options['value'] = $options['value'];
		}	elseif(isset($_POST[$field])) {
			$options['value'] = $_POST[$field];
		}	elseif(!isset($options['value']) && isset($options['default'])) {
			$options['value'] = $options['default'];
		}	else {
			$options['value'] = '';
		}

		$options['field'] = $field;
		if(!isset($options['placeholder'])) { $options['placeholder'] = ''; }

		if(in_array($type,array('submit','hidden')) || (isset($options['div']) && $options['div'] == 0)) {
			return $this->$type($options);
		}

		$input = $this->$type($options);
		$result = <<<EOT
<div class="form-group">
<label for="$field">$label</label>
$input
</div>
EOT;
		return $result;
	}

}

?>
