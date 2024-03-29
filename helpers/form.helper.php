<?
class form
{
	var $form_type;
	var $name;
	var $form_target;
	var $form_method;
	var $form_enctype;

	var $form_id;
	var $form_fields;
	var $form_result;

	var $class;

	var $field_box;
	var $form_prefix;

	var $multi_language;
	
	function form ()
	{
		$this->form_prefix = "f_";
	}

	public function form_header($action_target, $method_target='post',$id="", $validation=false, $js_actions=false,$enctype=null)	//Creates the Form Tag with action and method
	{

		if($action_target === NULL)
		{
			$form_action = "action='#' ";
		}
		else
		{
			$form_action = "action='$action_target' ";
		}
		
		$valid_action = (($validation===true)? "onsubmit=\"return Validator(this); return false;\" ":"");

		if($js_actions["onsubmit"] && $validation === true)	$valid_action = "onsubmit=\"return validHandler(this); return false;\" ";

		$method = "method='$method_target' ";
		$id_r=(!empty($id))?"id='$id' ":"";

		(!empty($enctype))? $enctype = "enctype='$enctype' ":null;

		if($js_actions !== false and !empty($js_actions))
		{
			foreach($js_actions as $action_type=>$action_script)
			{
				if($action_type != "onsubmit" OR $validation!==true)
				{
					$actions .= "$action_type=\"$action_script return false;\" ";
				}
			}
		}
		else
		{
			$actions = NULL;
		}

		$name = (!empty($this->name)?"name='{$this->name}' ":NULL);
		$f_class = (!empty($this->class)?"class='{$this->class}' ":NULL);
		return "<form $form_action$name$method$id_r$f_class$valid_action$actions$enctype>\n";	
	}



/*********************************************/
/*
/*
/*
/*
/*********************************************/
public function make_field($field)		//Calls the right function to create a field with the proper type, some own types are included
  {

    if(is_array($field))
    {
			if(isset( $field["type"] ) )
			{
				if( is_array($field["name"]) )
				{
					$name = $field["name"];

					if( $name["no_prefix"] )
					{
						$field["name"] = $name["value"];
					}
					else
					{
						$field["name"] = $this->form_prefix.$name["value"];
					}
				}
				else
				{
					$field["name"] = $this->form_prefix.$field["name"];
				}
  
        if(isset($field["label"]) && $field["type"] !== "hidden" /*&& $field["type"]!="radio"*/)
				{
					$label = $this->make_label($field); //"<label for=\"{$field["id"]}\">".$field["label"]."%s\n";
        }
				else
				{
					$label = "%s";
				}
				
        switch($field['type'])
        {
					case 'textarea': $field = $this->make_textarea($field); break;
          case 'select':  $field =$this->make_select($field); break;
          case 'checkbox': $field = $this->make_input_checkbox($field); break;
          case 'radio': $field =$this->make_input_radio($field); break;
					case 'button': $field = $this->make_input_button($field); break;
          default: $field = $this->make_input($field);
        }
  
				$result = sprintf($label,$field);
			}
			elseif( isset( $field["string"] ) )
			{
				$result = $field["string"];
			}
      
      return $result;
    }
    else
    {
      return false;
    }
  }
/*	public function make_field($field)		//Calls the right function to create a field with the proper type, some own types are included
  {
    //print_r($field);
    if(is_array($field))
    {
      if(isset($field["label"]))
			{
				$result = "<label for=\"{$field["id"]}\">".$field["label"]."\n";
      }
			$field["name"] = "f_".$field["name"];
      switch($field['type'])
      {
				case 'textarea': $result .= $this->make_textarea($field); break;
        case 'select': $result .= $this->make_select($field); break;
        case 'checkbox': $result .= $this->make_input_checkbox($field); break;
        case 'radio': $result .= $this->make_input_radio($field); break;
				case 'button': $result .= $this->make_input_button($field); break;
        default: $result .= $this->make_input($field);
      }
      if(isset($field["label"]))
      {
				$result .= "</label>\n";
      }
      
      return $result;
    }
    else
    {
      return false;
    }
  }*/



/*********************************************/
/*
/*
/*
/*
/*********************************************/
  public function create_output() //Lists the "ready to use" forms and calls the make_field function
  {
    $field_array = $this->form_fields;
    //print_r($field_array);
    if(isset($field_array['validation']))
    {
			$this->form_result .= $this->validation($field_array['validation'],$field_array["js_actions"]);
			$validate = true;
			unset($field_array['validation']);
    }
    else
    {
      $validate = false;
    }

		if(isset($field_array["js_actions"]))
		{
			$js_actions = $field_array["js_actions"];
		}
		else
		{
			$js_actions = NULL;
		}
		$this->form_method = (empty($this->form_method) || strtolower($this->form_method)=='post' ?'post':'get');
    $this->form_result .= $this->form_header($this->form_target,$this->form_method,$this->form_id, $validate, $js_actions,$this->form_enctype);

    $this->form_result .= $this->process_fieldset($field_array);
    $this->form_result .= "</form>";

		return $this->form_result;
  }


public function validation($val_data,$js_actions)
{
	$output .= "<script type='text/javascript'>\n";
	$output .= "<!--\n";
	
	if(count($js_actions)>0 && !empty($js_actions))
	{
		$output .= "function validHandler(theForm){\n";
			$output .= "\tvar valResult = Validator(theForm);\n";
			$output .= "\tif(valResult == true){\n";
				$output .= "\tfinalResult = true;\n";
				$output .= "\t}\n";
				$output .= "\telse{\n";
				$output .= "\treturn false;\n";
				$output .= "\t}\n";
		//	$i=1;
			foreach($js_actions as $action_script){
				$output .= "\tvar actionResult = $action_script\n";
				$output .= "\tif(actionResult == true){\n";
				$output .= "\tfinalResult = true;\n";
				$output .= "\t}\n";
				$output .= "\telse{\n";
				$output .= "\treturn false;\n";
				$output .= "\t}\n";
				
				//$i++;
			}
		$output .= "return finalResult;\n";
		$output .= "}\n";
	}

	if($val_data !== false)
	{
		
		$output .= "function Validator(theForm){\n";
		$output .= "var result = true;\n";
		//$output .= $val_data['directCode']."\n";

		$compare = $val_data['compare'];

		unset($val_data['compare'],$val_data['directCode']);

		foreach($val_data as $field=>$rule_alert)
		{
			if( substr( $field,0,6 ) != "radio_" )
			{
				$field_var = preg_replace("(([a-zA-Z]+)(\[([a-zA-Z-_]+)\]))","$1$3",$field);
				$field = "f_$field";
				list($rule,$alert) = explode("||",$rule_alert);

				$output .= "\tvar objRegExp = new RegExp('($rule)');\n"
					."//check if string matches pattern \n"
					."var $field_var = objRegExp.test(theForm.elements[\"$field\"].value);\n"
					."\tif ($field_var == false) {\n"
					."\ttheForm.elements[\"$field\"].style.background = '#900';\n";
  
				if(!empty($alert))
				{
					$output .= "\talert(\"$alert\");\n";
				}
				$output .= "\tresult = false";
				$output .= "\t}\n";
				$output .= "\telse {";
				$output .= "\ttheForm.elements[\"$field\"].style.background = '#fff';\n";
				$output .= "\t}\n";
			}
			else
			{
				$field = "f_".substr($field,6);
				list($rule,$alert) = explode("||",$rule_alert);
				$output .= "\t var inputRadios = theForm.elements[\"$field\"]; \n"
					."\t var radio_result = false; \n"
					."\t for (j=0; j < inputRadios.length; j++) \n"
					."\t { \n"
					."\t\t if( inputRadios[j].checked == true && inputRadios[j].value == $rule ) \n"
					."\t\t { \n"
					."\t\t\t radio_result = true; \n"
					."\t\t\t break; \n"
					."\t\t } \n"
					."\t } \n"
					."\t if(radio_result == true && result == true){result = true;} \n";
			}
		}
  
		if(!empty($compare))
		{
			list($rule,$alert) = explode( "||", $compare );
			$compare = explode( "=", $rule );
			$output .= "\tif (theForm.elements[\"f_{$compare[0]}\"].value != theForm.elements[\"f_{$compare[1]}\"].value) {\n";
			if( !empty( $alert ) )
			{
				$output .= "\talert('$alert');\n";
				$output .= "\tresult = false;";
			}
			$output .= "\t}\n";
		}
		$output .= "\treturn result;\n";
		$output .= "\t}\n";
	}
	$output .= "//-->\n";
	$output .= "</script>\n";
	
	return $output;
}
/*********************************************/
/*
/*
/*
/*
/*********************************************/
private function process_fieldset($array)
{
	(string)$output = "";

  if(isset($array["fieldset"]))
	{
      $fieldset=$array;		// Array renaming
      $output .= "<fieldset id='".$fieldset['fieldset']."'";	// Start fieldset
      if(isset($fieldset["class"])){$output .=" class='".$fieldset["class"]."'";}
      $output .= ">\n";
      if(isset($fieldset["legend"])){$output .="<legend>".$fieldset["legend"]."</legend>\n";}
      $output .= $this->process_fieldset($array['fields']);	// Recursive function calling, put list of fields or sub-fieldsets
      if(isset($fieldset["info"])){$output .="<p class='info'>".$fieldset["info"]."</p>";}
      $output .= "\n</fieldset>\n\n";		// Close fieldset
	}
	else
	{
	  //for($set=0;$set<count($array);$set++)	//Cycles through the array

		foreach($array as $item)
  	{
      //if(isset($array[$set]['fieldset']))	// if fieldset, create the tags a call itself
			if( isset($item['fieldset']))
      {
        $fieldset=$item;//$array[$set];		// Array renaming
        $output .= "<fieldset id='".$fieldset['fieldset']."'";	// Start fieldset
      if(isset($fieldset["class"])){$output .=" class='".$fieldset["class"]."'";}
      $output .= ">\n";
        if(isset($fieldset["legend"])){$output .="<legend>".$fieldset["legend"]."</legend>\n";}
        //$output .= $this->process_fieldset($array[$set]['fields']);	// Recursive function calling, put list of fields or sub-fieldsets
				$output .= $this->process_fieldset($item['fields']);	// Recursive function calling, put list of fields or sub-fieldsets
        if(isset($fieldset["info"])){$output .="<p class='info'>".$fieldset["info"]."</p>";}
        $output .= "\n</fieldset>\n\n";		// Close fieldset
      }
      else
      {
        $field = $item;//$array[$set];		// Array renaming
        $output .= $this->make_field($field);	//by calling the right function
      }
    }
	}
  return $output;		//returns the output
}

/*********************************************/
/*
/*
/*
/*
/*********************************************/
private function make_label($data)
	{
		$for_id = ( isset( $data["id"]) ? " for='".str_replace(array("[","]","(",")"),"",$data["id"])."'" : NULL);

		$name = str_replace(array("[","]","(",")"),"",$data["name"]);

		$label = $data["label"];
		(string)$params = "";
		if( is_array( $label ) )
		{
			$label_value = $label["value"];
			unset( $label["value"] );			

			foreach( $label as $param=>$value )
			{
				$params .= " $param = '$value'";
			}
		}
		else
		{
			$label_value = $label;
		}
	
		if($this->multi_language == true)
		{
			$label = "{{S ".strtolower(str_replace(" ","_",$label))."}}";
		}
		
		$helpButton = "";
		if( isset( $data['help'] ) && is_array($data['help']) )
		{
		 $help = $data['help'];
		 $helpButton = '<img src="templates/gfx/'.$help['icon'].'" title="'.$help['value'].'" />';
		}
		return "<label$for_id $params>$helpButton\n\t<span>".$label_value."</span>\n\t%s\n</label>\n";
	}

/*	private function make_label($data)
	{
		if( isset( $data["id"] ) and $data["type"] != "radio")
		{
			$for_id = " for='".str_replace(array("[","]","(",")"),"",$data["id"])."'";
		}
		$name = str_replace(array("[","]","(",")"),"",$data["name"]);
		$label = $data["label"];
	
		if($this->multi_language == true)
		{
			$label = "{{S ".strtolower(str_replace(" ","_",$label))."}}";
		}
		return "<label$for_id id='".$name."_label'>".$label."</label>\n";
	}*/

	private function make_textarea($params)		//Creates a normal Text field
	{
		//print_r($params);
		$label = $params['label'];
		$description = $params['description'];
		$textarea_value = htmlspecialchars($params['value']);
		unset($params['label'],$params['description'],$params['value'],$params["type"]);

		$field_result .= "<textarea ";		
		foreach($params as $key=>$value)
		{
			$field_result .= "$key='$value' ";
		}
		$field_result .= ">";
		$field_result .= $textarea_value;
		$field_result .= "</textarea>\n";
		$field_result .= "<span class='desc'>$description</span>";

		return $field_result;
	}


	private function make_input($params)		//Creates a form value reset field
	{
		$label = (isset($params['label'])?$params['label']:NULL);
		$description = (isset($params['description'])?$params['description']:NULL);
		unset($params['label'],$params['description']);
		
		$field_result = "<input ";
		$field_result .= $this->create_params($params);
		/*foreach($params as $key=>$value)
		{
			if($value=="text-date")
			{
				$value="text";
				$description = $time_action." ".$description;
			}
			$field_result .= "$key='$value' ";
		}*/
		$field_result .= "/>\n";
		if(!empty($description) && $params['type']!="hidden") $field_result .= "<span class='desc'>$description</span>";
		return $field_result;
	}


/*********************************************/
/*
/*
/*
/*
/*********************************************/
	private function make_input_button($params, $image=false)		//Creates a hidden field
	{
		$label = $params['label'];
		$description = $params['description'];
		unset($params['label'],$params['description'],$params["type"]);
		($image===false)?$type="button":$type="image";
		$field_result .= "<input type='$type' ";
		
		foreach($params as $key=>$value)
		{
			$field_result .= "$key='$value' ";
		}
		$field_result .= "/>\n";
		if(!empty($description)) $field_result .= "<span class='desc'>$description</span>";
		return $field_result;
	}


/*********************************************/
/*
/*
/*
/*
/*********************************************/
	private function make_input_checkbox($params)		//Creates a hidden field
	{
		$label = (isset($params['label'])?$params['label']:NULL);
		$description = (isset($params['description'])?$params['description']:NULL);
		
		if(empty($params["checked"]))
		{
			unset($params["checked"]);
		}

		unset($params['label'],$params['description'],$params["type"]);
		
		$field_result = "<input type='checkbox' ";
		
		foreach($params as $key=>$value)
		{
			$field_result .= "$key='$value' ";
		}
		$field_result .= "/>\n";

		if(!empty($description)) $field_result .= "<span class='desc'>$description</span>";
		return $field_result;
	}

/*********************************************/
/*
/*
/*
/*
/*********************************************/
	private function make_input_radio($params)		//Creates a hidden field
	{
		$description = $params['description'];
		$radio_name = $params["name"];
		$boxed = $params["boxed"];
		unset($params['label'],$params['description'], $params["name"],$params["boxed"],$params["type"]);
		foreach($params["value"] as $value=>$name)
		{
			(isset($params["checked"]) AND $params["checked"]==$value)?$checked = "checked='checked' ":$checked="";
			(isset($params["disabled"]))?$disabled = "disabled='disabled' ":$disabled="";
			$field_result .= "<label class='radioLabel'>$name\n<input type='radio' name='$radio_name' value='$value' $checked$disabled/>\n</label>\n";
		}
		if(!empty($description)) $field_result .= "<span class='desc'>$description</span>";
		return $field_result;
	}


/*********************************************/
/*
/*
/*
/*
/*********************************************/
	private function make_select($params)
	{
		$values = (isset($params["value"])?$params["value"]:NULL);
		$selected = (isset($params["selected"])?$params["selected"]:NULL);
		$description = (isset($params['description'])?$params['description']:NULL);
		$multiple = (isset($params['multiple'])?$params['multiple']:NULL);

		unset($params["label"], $params["value"], $params["selected"],$params["type"],$params['description']);

		$field_result = "<select$multiple";
		
		$field_result.= $this->create_params($params);
		$field_result.= ">\n";
		$field_result.= $this->make_select_options($values,$selected,(isset($params["opt_group"])?$params["opt_group"]:NULL));
		$field_result.= "</select>\n";
		if(!empty($description)) $field_result .= "<span class='desc'>$description</span>";
		return $field_result;
	}



/*********************************************/
/*
/*
/*
/*
/*********************************************/	
	private function make_select_options($data, $selected)		//creates a select field, with the right options
	{
		//unset($options_result);
		(string)$options_result = "";

		if(is_array($data))
		{
			foreach($data as $value=>$name)
			{	
				if(is_array($name) and isset($name["group"]))
				{					
					$group = $name;
					$options_result .= "<optgroup label=\"{$name["group"]}\">";
					
					foreach($group["value"] as $value=>$name)
					{
						
						if(is_array($selected))
						{
							(in_array($value,$selected)==true || in_array($name,$selected)==true)?$select=" selected='selected'":$select="";
						}
						else
						{
							($selected==$value OR $selected==$name)?$select=" selected='selected'":$select="";
						}
						$name = htmlentities($name,ENT_QUOTES,'UTF-8');
						$options_result.="<option value='$value'$select>$name</option>\n";
					}
					$options_result .= "</optgroup>";
				}
				else
				{
					if(is_array($selected))
					{
						(in_array($value,$selected)==true || in_array($name,$selected)==true)?$select=" selected='selected'":$select="";
					}
					else
					{
						($selected==$value OR $selected==$name)?$select=" selected='selected'":$select="";
					}
					$name = htmlentities($name,ENT_QUOTES,'UTF-8');
					$options_result.="<option value='$value'$select>$name</option>\n";
				}
			}
		}
		else
		{
			
			$options = explode(":",$data);
			foreach($options as $item)
			{
				$name = trim($item);
				if(strpos("||",$name))
				{
					list($value) = explode("||",$name);
				}
				else
				{
					$value=$name;
				}
				if(is_array($selected))
				{
					(in_array($name,$selected)==true)?$select=" selected='selected'":$select="";
				}
				else
				{
					($selected==$name)?$select=" selected='selected'":$select="";
				}
				$value = trim($value);
				$options_result.="<option$select value=\"$value\">$name</option>\n";
			}

		}
		return $options_result;
	}

/*********************************************/
/*
/*
/*
/*
/*********************************************/
	private function create_params($params)
	{
		if(is_array($params))
		{
			unset($output);
			(string)$output = "";
			foreach($params as $atrib=>$atr_value)
			{
			  if($atr_value=="text-date")
			  {
			    $atr_value="text"; 
			  }
				
				$output .= " $atrib='".str_replace("'","\"",$atr_value)."'";
			}

			return $output;
		}
		else
		{
			return false;
		}
	}

/*********************************************/
/*
/*
/*
/*
/*********************************************/


	public function auto_create_form($table,$cols=NULL)		// This function takes the information of a table and creates
	{																											// the right fields by passing the col-types and field-types together
		if(empty($cols))																	
		{			
			$db = new db(DB_SERVER, DB_USER, DB_PASSWD, DB_DATABASE);		//Connects to the database
			$cols =$db->db_get_cols($table);														//gets the col information for the right table
		}
		
		
		for($item=0;$item<count($cols);$item++)												//cycles through the fields and calls the make field
		{																															//functions to create the field
			if($cols[$item]['Default'] != "CURRENT_TIMESTAMP" || $cols[$item]['Extra'] != "auto_increment")
			{
				$name = ucfirst(strtolower($cols[$item]['Field']));
				switch($cols[$item]['Type'])
				{
					case (preg_match("(varchar)", $cols[$item]['Type'])?$cols[$item]['Type']:!$cols[$item]['Type']):
												$form .= "<label for='".$cols[$item]['Field']."'>$name</label>
																	<input type='text' name='".$cols[$item]['Field']."' id='".$cols[$item]['Field']."' /><br />"; 
												break;
					case ("float"):
												$form .= "<label for='".$cols[$item]['Field']."'>$name</label>
																	<input type='text' name='".$cols[$item]['Field']."' id='".$cols[$item]['Field']."' /><br />"; 
												break;
					case ("text"):
												$form .= "<label for='".$cols[$item]['Field']."'>$name</label>
																	<textarea name='".$cols[$item]['Field']."' id='".$cols[$item]['Field']."' rows='10' cols='50'>
																	</textarea>";
												break;
					case (preg_match("(int)", $cols[$item]['Type'])?$cols[$item]['Type']:!$cols[$item]['Type']):
												$form .= "<label for='".$cols[$item]['Field']."'>$name</label>
																	<input type='text' name='".$cols[$item]['Field']."' id='".$cols[$item]['Field']."' /><br />";
												break;
					case (preg_match("(set)", $cols[$item]['Type'])?$cols[$item]['Type']:!$cols[$item]['Type']):
												$value_start = strpos($cols[$item]["Type"],"(")+1;
												$value_stop = strpos($cols[$item]["Type"],")")-1;
												$value_string = substr($cols[$item]["Type"],$value_start,-1);
												$form .= "<label for='".$cols[$item]['Field']."'>$name</label>
																	<select name=\"{$cols[$item]['Field']}\">{$this->make_select_options($value_string,0)} </select>";
												break;
				}
			}
		}

		$this->form_result= $form;
	}

	public function print_output()
	{
		print($this->form_result);
	}
	
	public function clear()
	{
		unset($this->form_target,$this->form_method,$this->form_id,$this->form_fields,$this->form_result);
	}

	private function is_assoc($array) 
	{
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}
	
}
?>
