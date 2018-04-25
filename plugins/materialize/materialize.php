<?php

class materialize extends rcube_plugin {
	//public $task = 'mail|settings';
	
	function init() {
		
		$this->load_config();

		$rcmail = rcmail::get_instance();
		
		$this->add_texts('localization/');
		
		$this->include_script('materialize.min.js');
		$this->include_stylesheet('materialize.min.css');
		
		$this->include_script('mobile.js');
		$this->include_stylesheet('mobile.css');

		$this->add_hook('template_object_materialize-maillist', array($this, 'uiMailList'));
		$this->add_hook('template_object_materialize-inbox', array($this, 'uiInbox'));
		$this->add_hook('template_object_materialize-folders', array($this, 'uiFolders'));
		$this->add_hook('template_object_materialize-messageheaders', array($this, 'messageHeaders'));
		$this->add_hook('template_object_materialize-avatar', array($this, 'avatar'));
		$this->add_hook('template_object_materialize-copyright', array($this, 'copyright'));

		if ($rcmail->task == 'mail') {
			$this->add_hook('message_headers_output', array($this, 'message_headers_output'));
		}
		
		//javascript interfacing
		$this->register_action('plugin.reloadmore', array($this, 'reloadmore'));

	}
	
	function copyright($args) {
		$rcmail = rcmail::get_instance();
		$args['content']=$rcmail->config->get('copyright');
		return $args;
	}
	
	function messageHeaders($args) {
		
		$rcmail = rcmail::get_instance();
		$mbox = rcube_utils::get_input_value('_mbox',rcube_utils::INPUT_GET);
		$uid = rcube_utils::get_input_value('_uid',rcube_utils::INPUT_GET);
		
		$message = $rcmail->get_storage()->get_message_headers($uid,$mbox);
		
		$parsedFrom = htmlentities($message->from);
		$parsedTo = htmlentities($message->to);
		$parsedCc = htmlentities($message->cc);
		$parsedDate = htmlentities($message->date);
		
		//build more details
		
		$liTo = "<ul>";
		foreach (explode(",",$parsedTo) as $iTo) {
			$liTo .= "<li>" . $iTo . "</li>";
		}
		$liTo .= "</ul>";
		
		$tabCc="";
		if (!empty($message->cc)) {
			
			$liCc = "<ul>";
			foreach (explode(",",$parsedCc) as $iCc) {
				$liCc .= "<li>" . $iCc . "</li>";
			}
			$liCc .= "</ul>";
			
			$tabCc   = "<tr><td>Cc:</td><td>" . $liCc . "</td></tr>";
		}
		
		$date = new DateTime($parsedDate);
		$dateStr = $date->format('j M Y, g:i A');
		
		$allRecip = $message->to;
		
		$tabFrom = "<tr><td>From:</td><td>" . $parsedFrom . "</td></tr>";
		$tabTo   = "<tr><td>To:</td><td>" . $liTo . "</td></tr>";
		$tabDate = "<tr><td>Date:</td><td>" . $dateStr . "</td></tr>";
		
		$table = "<table><tbody>". $tabFrom . $tabTo . $tabCc . $tabDate . "</tbody></table>";
		$moredetails = "<div class='customcollapse'>".$table."</div>";
		
		
		if (!empty($message->cc)) {
			$allRecip .= "," . $message->cc;
		}
		
		$recipients = explode(",",$allRecip);
		
		if (count($recipients)>1) {
			$strTo = "";
			foreach($recipients as $r) {
				
				$r = trim($r);
				
				if (strpos($r,"<")) {
				
					$str = explode("<",$r);
					if (empty($str[0])) $strTo .= explode("@",$str[1])[0];
					else if (strpos($r,$_SESSION["username"])) $strTo .= "me";
					else {
						$str[0] = str_replace("'","",$str[0]);
						$str[0] = str_replace('"',"",$str[0]);
						
						$strTo .= explode(" ",$str[0])[0];
					}
				
				} else {
					$strTo .= explode("@",$r)[0];
				}				
				
				$strTo .= ", ";
				
			}			
				$strTo = substr($strTo,0,strlen($strTo)-2);
			}
			
		else $strTo = "me";
		
		$avatar=$this->senderAvatar($message->from);
		$img   = "<img src='images/avatar.jpg' alt='' class='circle'>";
		$span  = "<span class='email-from truncate'>" . $message->from . "</span>";
		$pTo   = "<p class='truncate grey-text ultra-small'>to " . $strTo . "</p>";
		$pTime = "<p class='grey-text ultra-small'><span id='time-header'>" . $this->getDate($message->date) . "</span>&nbsp;" . "<span class='btnCollapse blue-text'></span></p>";
		
		$headers = $avatar . $span . $pTo . $pTime . $moredetails;
		
		$args["content"]=$headers;
		
		return $args;
		
	}

	function uiMailList($args) {
		
		$_SESSION["reloadpage"]=1;
		
		$rcmail = rcmail::get_instance();
		$mbox = rcube_utils::get_input_value('_mbox',rcube_utils::INPUT_GET);
		$x = $rcmail->get_storage()->index($mbox);
		$_SESSION["mailindices"]=$x->get();
		$args['content']=$this->buildMessageList();
		
		return $args;
	}
	
	//cannot put $args, it complains no of arguments
	function reloadmore() {
		$rcmail = rcmail::get_instance();
		$_SESSION["reloadpage"]++;
		$_SESSION["mailindices"] = array_slice($_SESSION["mailindices"],10);
		$messagelist = $this->buildMessageList();
		$rcmail->output->command('plugin.reloadmore_cb',array('status'=>'0','page'=>$this->reloadpage,'messagelist'=>$messagelist));	
	}
	
	function buildMessageList() {
		
		$delimeter=".";	
		$rcmail = rcmail::get_instance();	
		$mbox = rcube_utils::get_input_value('_mbox',rcube_utils::INPUT_GET);
		$page = rcube_utils::get_input_value('_page',rcube_utils::INPUT_GET);	
		
		if(!$page) $page=1;
		
		$x = array_slice($_SESSION["mailindices"],0,10);
		
		if(empty($x)) $li="<p class='center-align'>No more message.</p>";
		else {
			$li='';
			foreach($x as $y) {
				
				$messages = $rcmail->get_storage()->get_message_headers($y,$mbox);
				
				$sender = $messages->from;
				$sender = str_replace('"', "", $sender);
				$sender = str_replace("'", "", $sender);
		
				$unread="email-unread";
				if ($messages->flags["SEEN"]) $unread="";
		
				$avatar=$this->senderAvatar($messages->from);
				$from="<span class='truncate ultra-small email-title'>".$messages->unread.$sender."</span>";
				$subject="<p class='truncate grey-text ultra-small email-subject'>".$messages->subject."</p>";
				
				//multipart/mixed probably inaccurate
				if ($messages->ctype=="multipart/mixed") $attach="<i class='tiny material-icons grey-text email-attachment'>attachment</i>";
				else $attach="";
				
				$date="<span class='secondary-content email-time'>".$attach."<span class='grey-text ultra-small'>". $this->getDate($messages->date) . "</span></span>";
				
				$a = "<a class='email-list' href='./?_task=mail&_mbox=".$mbox."&_uid=".$y."&_action=show' >";
				$a_close = "</a>";
				
				$li.="<li class='collection-item avatar grey lighten-4 waves-effect waves ".$unread."'>". $a . $avatar . $from . $subject . $date . $a_close. "</li>";
		
		
			}
		
			$loadmore = "<p class='center-align'><a id='loadmore' data-page='".$_SESSION["reloadpage"]."' class='waves-effect waves btn-flat'>Load More</a></p>";
			$li .=$loadmore;
		}
		
		return $li;
		
	}
	
	function uiInbox($args) {
		
		$rcmail = rcmail::get_instance();
		$subscribed_folders = $rcmail->get_storage()->list_folders();
		return $args;
	}
	
	function uiFolders($args) {
	
		$rcmail = rcmail::get_instance();
		
		$new = $rcmail->folder_list(array());

		require_once($this->home . '/phpQuery/phpQuery.php');
		$doc = phpQuery::newDocumentXHTML($new);
		
		//find opened folder
		$uri = htmlspecialchars($_SERVER['REQUEST_URI']);
		$mbox = substr($uri,strpos($uri,"INBOX."));
		
		$find = strpos($new,"unreadcount");	
		
		$doc['.treetoggle']->parent()->children(":first-child")->removeAttr("onclick");	

		$doc['.treetoggle']->parent()->addClass("active");
		$doc['.treetoggle']->parent()->children(":first-child")->addClass("collapsible-header waves-effect waves-blue");

		$doc['.treetoggle']->parent()->children(":first-child")->append($find." <i class='material-icons right' style='margin-right:0;'>arrow_drop_down</i>");
		$doc['.treetoggle']->parent()->wrap("<ul class='collapsible' data-collapsible='collapsible'></ul>");
		$doc['.treetoggle']->parent()->children("ul")->wrap("<div class='collapsible-body'></div>");
		$doc['[role=group]']->wrap("<div class='collapsible-body'></div>");
		$doc['.treetoggle']->remove();
		$doc['.collapsible li']->removeClass("active");
		
		//assign folder icons to all folders
		$doc['a[rel^="INBOX."]']->prepend("<i class='material-icons'>folder</i>");
		
		//assign icons to common folders
		$doc['.inbox a']->prepend("<i class='material-icons'>inbox</i>");
		$doc['.drafts a .material-icons']->text("edit");
		$doc['.sent a .material-icons']->text("send");
		$doc['.junk a .material-icons']->text("block");
		$doc['.trash a .material-icons']->text("delete");
		
		$doc['a[rel$="INBOX.Archive"] .material-icons:first-child']->text("archive");
		
		//make opened folder active
		$subs = "";
		$folders = explode(".",$mbox);
		foreach($folders as $folder) {
			$subs.=$folder;
			$doc['a[rel="'.$subs.'"]']->addClass("active");
			$subs.=".";
		}
		
		$doc['a span.unreadcount']->addClass("new badge");

		//settings,addressbook,etc in mobile.js
		

		$args['content']=$doc->htmlOuter();
		
		return $args;
	}
	
	function getDate($datetime) {
		
		$date = new DateTime($datetime);
		$curdate = new DateTime("today");
		
		$curyear = $curdate->format('Y');
		$year = $date->format('Y');
		
		if($date>$curdate) return $date->format('g:i a');
		if($year<$curyear) return $date->format('d/m/Y');
		return $date->format('M j');
		
	}
	
	function senderAvatar($sender) {
		
		$sender = str_replace('"', "", $sender);
		$sender = str_replace("'", "", $sender);

		$emailfinder = preg_match("/[a-zA-Z0-9._-]+@/",$sender,$matches);
		$dotfinder = preg_split("/\./",$matches[0]);
				
		if (count($dotfinder)>1) {
			$res = strtoupper(substr($dotfinder[0],0,1)) . strtoupper(substr($dotfinder[1],0,1));
		}
		else 
			$res = strtoupper(substr($matches[0],0,1)) . strtoupper(substr($matches[0],1,1));
		
		$color["A"]="red darken-1";
		$color["B"]="purple darken-1";
		$color["C"]="indigo darken-1";
		$color["D"]="light-blue darken-1";
		$color["E"]="teal darken-1";
		$color["F"]="light-green darken-1";
		$color["G"]="yellow darken-1";
		$color["H"]="orange darken-1";
		$color["I"]="brown darken-1";
		$color["J"]="pink darken-1";
		$color["K"]="deep-purple darken-1";
		$color["L"]="blue darken-1";
		$color["M"]="cyan darken-1";
		$color["N"]="green darken-1";
		$color["O"]="lime darken-1";
		$color["P"]="amber darken-1";
		$color["Q"]="deep-orange darken-1";
		$color["R"]="grey darken-1";
		$color["S"]="red darken-4";
		$color["T"]="purple darken-4";
		$color["U"]="indigo darken-4";
		$color["V"]="light-blue darken-4";
		$color["W"]="teal darken-4";
		$color["X"]="light-green darken-4";
		$color["Y"]="yellow darken-4";
		$color["Z"]="orange darken-4";
			
		$avatar="<span class='circle " . $color[$res[0]] . " center-align' id='avatar-none'>".$res."</span>";
		
		return $avatar;
		
	}

}
