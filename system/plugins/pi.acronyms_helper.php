<?php

$plugin_info = array(
	'pi_name'        => 'Acronyms Helper',
	'pi_version'     => '1.0',
	'pi_author'      => 'Nathan Pitman',
	'pi_author_url'  => 'http://ninefour.co.uk/labs',
	'pi_description' => 'Display an index or list of acronym entries from the Solspace Acronyms Module',
	'pi_usage'       => Acronyms_helper::usage()
);

class Acronyms_helper {

	// Index tag
	function index() {
			
		global $DB, $TMPL;

		$acronym_group_name = $TMPL->fetch_param('acronym_group_name');
		$return = '';

		if($acronym_group_name != "") {
			$sql = "SELECT * FROM exp_acronym_group 
					WHERE acronym_group_name ='".$DB->escape_str($acronym_group_name)."'";
			$query_group = $DB->query($sql);
			unset($sql);
			
			$sql = "SELECT left(acronym_self,1) AS acronym_letter, COUNT(acronym_id) AS count FROM exp_acronym WHERE acronym_group_id ='".$query_group->row['acronym_group_id']."' GROUP BY left(acronym_self,1)	ORDER BY acronym_self ASC ";
		} else {
			return false;
		}
		
		$query = $DB->query($sql);
		unset($sql);

		if ($query->num_rows == 0) {
			return false;
		}
		
		foreach ($query->result as $row) {

			$tagdata = $TMPL->tagdata;
			
			foreach ($TMPL->var_single as $key => $val) {
							
				if($key == 'acronym_letter') {
					$tagdata = $TMPL->swap_var_single(
						$key, 
						$row['acronym_letter'], 
						$tagdata
						);
				}
				
				if($key == 'count')	{
					$tagdata = $TMPL->swap_var_single(
						$val, 
						$row['count'], 
						$tagdata
						);
				}
							
			}

			$return .= $tagdata;

		}

		if ($backspace != '')
		{
			$return = substr($return, 0, - $backspace);
		}

		return $return;

	}

	
	
	// Entries tag
	function entries() {
	
		global $DB, $FNS, $TMPL;

		$acronym_group_name = $TMPL->fetch_param('acronym_group_name');
		$limit = $TMPL->fetch_param('limit');
		$return = '';

		if($acronym_group_name != "") {
			$sql = "SELECT * FROM exp_acronym_group 
					WHERE acronym_group_name ='".$DB->escape_str($acronym_group_name)."'";
			$query_group = $DB->query($sql);
			unset($sql);
			
			$sql = "SELECT left(acronym_self,1) AS acronym_letter, acronym_self, acronym_description, COUNT(acronym_id) AS count FROM exp_acronym WHERE acronym_group_id ='".$query_group->row['acronym_group_id']."'";
		} else {
			return false;
		}

		$sql .= " GROUP BY acronym_id 
				ORDER BY acronym_self ASC ";

		if (is_numeric($limit))	{
			$sql .= " LIMIT 0, " . $DB->escape_str($limit);
		}

		$query = $DB->query($sql);
		unset($sql);

		if ($query->num_rows == 0) {
			return false;
		}
		
		$previous_letter = "";

		foreach ($query->result as $row) {

			$tagdata = $TMPL->tagdata;
			
			if ($row['acronym_letter']!=$previous_letter) {
				$cond['first_letter'] = 1;
			} else {
				$cond['first_letter'] = 0;
			}
			
			$tagdata = $FNS->prep_conditionals($tagdata, $cond);

			foreach ($TMPL->var_single as $key => $val) {

				if (ereg("^switch", $key)) {
					$sparam = $TMPL->assign_parameters($key);
					
					$sw = '';

					if (isset($sparam['switch'])) {
						$sopt = explode("|", $sparam['switch']);
						
						if (count($sopt) == 2) {
							if (isset($switch[$sparam['switch']]) AND $switch[$sparam['switch']] == $sopt['0']) {
								$switch[$sparam['switch']] = $sopt['1'];
								$sw = $sopt['1'];
							} else {
								$switch[$sparam['switch']] = $sopt['0'];
								$sw = $sopt['0'];
							}
						}
					}
					
					$tagdata = $TMPL->swap_var_single($key, $sw, $tagdata);
				}
				
				if($key == 'acronym_letter') {
					$tagdata = $TMPL->swap_var_single(
						$key, 
						$row['acronym_letter'], 
						$tagdata
						);
				}

				if($key == 'acronym_self') {
					$tagdata = $TMPL->swap_var_single(
						$key, 
						$row['acronym_self'], 
						$tagdata
						);
				}
				
				if($key == 'acronym_description') {
					$tagdata = $TMPL->swap_var_single(
						$key, 
						$row['acronym_description'], 
						$tagdata
						);
				}

				if($key == 'count') {
					$tagdata = $TMPL->swap_var_single(
						$val, 
						$row['count'], 
						$tagdata
						);
				}

			}

			$previous_letter = $row['acronym_letter'];

			$return .= $tagdata;

		}

		return $return;

	}

function usage() {
	ob_start(); 
?>

This plug-in is designed to complement the Solspace Acronym Module by providing tags which can be used to output a list of all acronyms and an alphabetical list of letters used in the Acronym index.

The plug-in has two tag pair types; 'index' and 'entries'.

{exp:acronyms_helper:index}

This tag pair has one parameter:

 - acronym_group_name (Required) - The name of the acronym group which you wan to output an index for.
 
This tag pair has two variables:

 - {acronym_letter}
 - {count}
 
Example usage:
 
<ul id="Index">
	<li><strong>Index:</strong> </li>
	{exp:acronyms_helper:index acronym_group_name="Standard"}
	<li><a href="{acronym_letter}" title="{acronym_letter}">{acronym_letter}</a></li>
	{/exp:acronyms_helper:index}
</ul>

{exp:acronyms_helper:entries}

This tag pair has two parameters:

 - acronym_group_name (Required) - The name of the acronym group which you wan to output an index for.
 - limit - You an optionally specifiy a limit on the number of acronyms to return.
 
This tag pair has four variables:

 - {acronym_letter}
 - {acronym_self}
 - {acronym_description}
 - {count}
 
With this tag pair you can also use the {if first_letter} conditional to show the first letter only when the letter changes, this is handy if you want to have a letter heading for each logical block of acronyms.
 
Example usage:
 
{exp:acronyms_helper:entries acronym_group_name="Standard"}
	{if first_letter}<a name="{acronym_letter}"></a><h3>{acronym_letter}</h3>{/if}
	<h4>{acronym_self}</h4>
	<p>{acronym_description}</p>
{/exp:acronyms_helper:entries}

<?php
	$buffer = ob_get_contents();
	
	ob_end_clean(); 

	return $buffer;
}

// END
}

?>