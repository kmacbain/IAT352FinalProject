<?php
session_start();
	include('functions.php');
	$database=db_connect($dbhost,$dbuser,$dbpass,$dbname);

	//activate when a request is received from javascript
	if(isset($_GET['query'])){
		//do accordingly based on the type of request
		switch($_GET['query']){
			//for index.html, pulling posts for index page once the page starts, and act accordingly based on user filter
			case 'loadIndex':
				//prepare query: pull post_id that are needed from database based on filter requirements
				$query_select="posts.post_id";
				$query_from="posts";
				$query_where="";			
				if(isset($_GET['filter']) && $_GET['filter']!='none'){

					switch($_GET['filter']){
						case 'images':
							$query_where.='posts.category=1';
							break;
						case 'articles':
							$query_where.='posts.category=2';
							break;
						case 'follow':
							if(isset($_GET['user'])){
								$user=$_GET['user'];
								$query_from.=",(SELECT following.followed_user_id FROM following WHERE following.user_id=".$user.") AS idsrc";
								$query_where.="posts.user_id=idsrc.followed_user_id";
							}
							break;
					}
				}			
				if(isset($_GET['tag']) && $_GET['tag']!='none'){
					// "SELECT posts.post_id FROM posts, tags WHERE tags.tag_name='".$_GET['tag']."' AND tags.post_id=posts.post_id"
					$query_from.=",tags";
					if($query_where==""){
						$query_where.="tags.tag_name='".$_GET['tag']."' AND tags.post_id=posts.post_id";
					}
					else{
					$query_where.=" AND tags.tag_name='".$_GET['tag']."' AND tags.post_id=posts.post_id";
					}
				}
				$query_where.=" ORDER BY upload_time DESC";
				if($query_where==" ORDER BY upload_time DESC"){
					$query1="SELECT ".$query_select." FROM ".$query_from." ".$query_where;
				}
				else{
					$query1="SELECT ".$query_select." FROM ".$query_from." WHERE ".$query_where;
				}			
				//prepare query: pull all if no filter is set
				//use the query prepared above to pull the list of post_id, and put in an array for future use
				$result1=$database->query($query1);
				$output_postID_list=[];
				for($i=0;$i<$result1->num_rows;$i++){
					$output_each=$result1->fetch_row();
					array_push($output_postID_list,$output_each[0]);
				}
				//once received the list of post id, call a function to further pull the details of those post id and then generate the html layout 
				$output_final=pullPosts($output_postID_list,$database);
				echo json_encode($output_final);
				break;

			//for user-profile.html
			case 'loadUser':
				if(isset($_GET['uid'])){

					//load user bio: step 1 pulling user bio data from database
					$uid=$_GET['uid'];
					$query1="SELECT users.user_id,users.user_name,users.avatar,users.album_cover,users.description, COUNT(following.followed_user_id) AS followers FROM users, following WHERE users.user_id=".$uid." AND users.user_id=following.followed_user_id";

					$result1=$database->query($query1);
					$output1=$result1->fetch_assoc();
					//load user bio: step 2 assign data to html layout, store in a variable for future use
					$v1= '
		                <section class="banner">
		                    <!-- img or grey background-->
		                    <img src="uploads/images/'.$output1['album_cover'].'">
		                </section>
		                <section class="bio flex flex-row">
		                	<div class="flex flex-column section-bio-left">
			                    <img id="profile-avatar" src="uploads/images/'.$output1['avatar'].'">
			                    <button id="button-index-Following" class="selected" type="follow" value="follow"><strong>Follow</strong></button>
		                    </div>

		                   	<div class="flex flex-column section-userInfo-text">
			                    <div class="flex flex-row">
				                    <h1>'.$output1['user_name'].'</h1>
				                    <p>@'.$output1['user_id'].'</p>
				          			<p>|</p>
				                    <p class="follower">'.$output1['followers'].' followers</p>
			                    </div>
			                    
			                    <p class="user-desc">'.$output1['description'].'</p>
		                    <div>
		                </section>
					';

					//load posts (user's posts or user's collections)
					//step 1:design a query that will get the post id of posts that need to be displayed
					if(isset($_GET['type'])){
						$type=$_GET['type'];
						$query_select="";
						$query_from="";
						$query_where="";
						//specifiy the base for sql query based on whether user want to view this member's post or this member's collection
						if($type=='posts'){
							$query_select="posts.post_id";
							$query_from="posts";
							$query_where="posts.user_id=".$uid;
						}
						elseif($type=='collection'){
							$query_select="posts.post_id";
							$query_from="posts,collection";
							$query_where="posts.post_id=collection.post_id AND collection.user_id=".$uid;		
						}
						//TBD: error handling: set an else{} statement to cover cases where type does not equal to any of those values stated above

						//if user is filtering by tag, add corresponding commands to the query
						if(isset($_GET['tag']) && $_GET['tag']!='none'){
							$query_from.=",tags";
							$query_where.=" AND tags.tag_name='".$_GET['tag']."' AND tags.post_id=posts.post_id";
						}
						//if user is filtering by image/article type, add corresponding commands to the query
						if(isset($_GET['filter']) && $_GET['filter']!='none'){
							$filter='';
							switch($_GET['filter']){
								case 'images':
									$filter=' AND category=1';
									break;
								case 'articles':
									$filter=' AND category=2';
									break;
							}
							$query_where.=$filter;
						}
						//specifiy that result should be ordered by upload time and finish the query by putting all components together
						$query_where.=" ORDER BY upload_time DESC";
						$query1="SELECT ".$query_select." FROM ".$query_from." WHERE ".$query_where;
						// echo $query1;
					}

					//load posts step 2: pull post information needed for the final layout based on the list of post_id, and return an array of information		
					$result1=$database->query($query1);
					$output_postID_list=[];
					for($i=0;$i<$result1->num_rows;$i++){
						$output_each=$result1->fetch_row();
						array_push($output_postID_list,$output_each[0]);
					}

					///////////////////////////////////////////////////////
					$v2=pullPosts($output_postID_list,$database);
					//////////////////////////////////////////////////////


					$arr=['bio' => $v1,'userpost' => $v2];

					//instead of echoing raw html, the generated html layout is now transfered back to frontend in JSON format. JSON format allows more flexible manipulation for the front end.	
					//In this case the array $arr containing two modules (user bio and user posts), which are raw HTML codes stored in array. It is tranfered back to the front end. The front end can now insert those two modules into different parts of the page, which cannot be done if php simply transfer pure html.			
					echo json_encode($arr);
				}
				break;

			//for detail.html
			case 'loadPostDetail':
				if(isset($_GET['post'])){

					$post_id=$_GET['post'];
					//prepare query for major components
					$query_main="SELECT posts.post_id,posts.category,COUNT(collection.post_id) AS collec_num,posts.description,users.user_id,users.user_name,users.avatar FROM posts,collection,users WHERE posts.post_id=".$post_id." AND posts.post_id=collection.post_id AND posts.user_id=users.user_id";
					$result_main=$database->query($query_main);
					$output_main=$result_main->fetch_assoc();
					
					//prepare image query
					$query_img="SELECT images.image_content FROM posts,images WHERE posts.post_id='".$post_id."' AND posts.post_id=images.post_id;";	
					$result_img=$database->query($query_img);
					$output_img=$result_img->fetch_assoc();

					//prepare comment number query
					$query_commNum="SELECT COUNT(comments.post_id) AS comment_num FROM posts,comments WHERE posts.post_id='".$post_id."' AND posts.post_id=comments.post_id;";	
					$result_commNum=$database->query($query_commNum);
					$output_commNum=$result_commNum->fetch_assoc();

					//function for pulling view count done by Kai-Lee
					//TBD, fix view count with this
					$query_viewCount="SELECT views.view_count FROM posts,views WHERE posts.post_id='".$post_id."' AND posts.post_id=views.post_id;";
					$result_view=$database->query($query_viewCount);
					$output_view=$result_view->fetch_assoc();

					//prepare tag query
					$query_tag="SELECT posts.post_id,tags.tag_name FROM posts,tags WHERE posts.post_id=".$post_id." AND posts.post_id=tags.post_id";
					$result_tag=$database->query($query_tag);
					$output_tag=[];

					//pull data
					for($i=0;$i<$result_tag->num_rows;$i++){
						$output_tag_each=$result_tag->fetch_assoc();
						array_push($output_tag,$output_tag_each);
					}
					$v2='<p>';
					if(count($output_tag)!=0){
						for($i=0;$i<count($output_tag);$i++){
							$v2.= '#'.$output_tag[$i]['tag_name'].' ';
						}
					}
					else{
						$v2.='no tags available';
					}
					$v2.= '</p>';

					//prepare query for list of comments
					$query_comment="SELECT posts.post_id,users.user_id,users.avatar,users.user_name,comments.content,comments.upload_time FROM posts,users,comments WHERE posts.post_id=".$post_id." AND posts.post_id=comments.post_id AND users.user_id=comments.user_id";
					$result_comment=$database->query($query_comment);
					$output_comment=[];
					for($i=0;$i<$result_comment->num_rows;$i++){
						$output_comment_each=$result_comment->fetch_assoc();
						array_push($output_comment,$output_comment_each);
					}
					$output_comment_final=[];
					//pull data
					for($i=0;$i<count($output_comment);$i++){
						$output_each=$output_comment[$i];
						$v='
	                    <section class="section-commentUnit flex flex-row">
	                        <img src="uploads/images/'.$output_each['avatar'].'">
	                        <div class="section-comment-content flex flex-column">
	                            <div class="flex flex-row flex_center_align_horizontal">
	                                <a href="user-profile.html?userid='.$output_each['user_id'].'">'.$output_each['user_name'].'</a>
	                                <p class="date"> Posted on '.$output_each['upload_time'].'</p>
	                            </div>
	                            <p>'.$output_each['content'].'</p>  
	                        </div>                      
	                    </section>
	                    ';
	                    array_push($output_comment_final,$v);
					}

					//check if this post is collected, helps to assign correct style to collection button
	                if(isset($_GET['loginID'])){
	                    $loginID=$_GET['loginID'];

	                    if(isCollected($loginID,$output_main['post_id'],$database)){
	                        $output_checkCollect=['collected'=>'yes'];
	                    }
	                    else{
	                        $output_checkCollect=['collected'=>'no'];
	                    }
	                    $output_main=array_merge($output_main,$output_checkCollect);
	                }          
	

					$v1=generatePostDetail($output_main,$output_img,$output_commNum,$output_view);
					$v_uploaderid=$output_main['user_id'];
					//different parts of the page are pulled in different queries. after pulled, the pulled data will be stroed in corresponding arrays. those arrays will be put into one final array and sent back to the front end
					$arr=['main'=>$v1, 'tag'=>$v2, 'comment'=>$output_comment_final, 'uploader'=>$v_uploaderid];


						// View Count increment based on session, added by Kai-Lee MacBain
						// Adds a view count to the table for the current post id, but only if the current browsing session hasn't already incremented it yet
						// (So people can't keep refreshing the page to spam view count)
							// Help understanding how to view the network inspector window so I can find what error the code is throwing from Bri MacBain
							// Solution for error found here: https://stackoverflow.com/questions/18797251/notice-unknown-skipping-numeric-key-1-in-unknown-on-line-0
						if(isset($_SESSION["viewcount_".$post_id])){
							// Do nothing - we've already viewed this post, and a session variable has been created to remember that and not increment view count further
						}
						// Otherwise we need to increment the view counter table for this post and set a session variable to prevent multiple view counts in same session for same post
						else{
							// Insert a new row into Views table for the post id, or update the row if a view count for post id already exists
							$query_viewCountIncrement = "
							INSERT INTO views (post_id, view_count)
							VALUES (".$post_id.", 1)
							ON DUPLICATE KEY UPDATE view_count = view_count + 1;";
							$result_viewCount=$database->query($query_viewCountIncrement);

							// Now set session variable for this post id view count, so we don't keep repeating view increments for this session
							// Code for how to avoid repeat counts for the same session from:
							// https://stackoverflow.com/questions/12778277/avoid-page-counting-when-click-refresh-button
							$_SESSION["viewcount_".$post_id] = 1;
						}


					echo json_encode($arr);
				}		
				break;

			case 'addCollection':
				if(isset($_GET['post']) && isset($_GET['user'])){
					$userid=$_GET['user'];						
					$post_id=$_GET['post'];

					//by Winkie
                    $query_addCollection = "INSERT INTO collection (user_id, post_id)";
                    $query_addCollection.=" VALUES(?,?)";
                    $stmt=$database->prepare($query_addCollection);
                    $stmt->bind_param('ii',$userid,$post_id);
                    if($stmt->execute()){ echo 'success'; }
                    else{ echo 'fail'; }   
                    //by Winkie END         
				}			
				break;
			case 'removeCollection':
				if(isset($_GET['post']) && isset($_GET['user'])){
					$userid=$_GET['user'];						
					$post_id=$_GET['post'];

					$query_deleteCollect="DELETE FROM collection WHERE collection.user_id=".$userid." AND collection.post_id=".$post_id;
                    $result_deleteCollect=$database->query($query_deleteCollect);
                    if($result_deleteCollect){ echo 'success'; }
                    else{ echo 'fail'; }   				
                }				
				break;
		}

	}//END if(isset($_GET['request']))//////////////////


?>

