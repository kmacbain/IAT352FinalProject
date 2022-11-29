<?php
//database connection///////////////////////////////////////////////////////////
	$dbhost='localhost';
    $dbuser="root";
    $dbpass="";
    $dbname="garage";	

    function db_connect($dbhost,$dbuser,$dbpass,$dbname){
        $connect=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
        return $connect;
    }
    function db_disconnect($connection){
        if(isset($connection)){
            mysqli_close($connection);
        }
    }

    //Function to sanitize inputs before sql inserts - by Kai-Lee MacBain, adapted from code from Lecture 8
    function sanitizeInputForSql($dbConnection,$var) {
        $var = mysqli_real_escape_string($dbConnection, $var);
        $var = htmlentities($var);
        $var = strip_tags($var);
        return $var;
    }

//post generation////////////////////////////////////////////////////////////////
    function generatePosts($array){
        $output_arr_final=$array;
        $final_result=[];
        for($i=0;$i<count($output_arr_final);$i++){
            $output_each=$output_arr_final[$i];
            if($output_arr_final[$i]['category']==1){
                $v= '
                <div class="flex flex-column section-userWorkDisplay-Box">
                <div class="flex flex-row section-uploaderInfo">
                <a href="user-profile.html?userid='.$output_each['user_id'].'" class="flex flex-row flex_center_align_horizontal">
                <img src="uploads/images/'.$output_each['avatar'].'">
                <h4>'.$output_each['user_name'].'</h4>
                </a>
                <p>'.$output_each['upload_time'].'</p>
                </div>
                <div class="flex flex-column section-workdisplay">
                <a href="detail.html?post='.$output_each['post_id'].'">
                <img src="uploads/images/'.$output_each['image_content'].'">
                </a>
                </div>      
                <div class="flex flex-row section-workInteractButtons">
                <button><img src="img/icon-like.svg">'.$output_each['collec_num'].'</button>
                <button><img src="img/icon-comment.svg">'.$output_each['comment_num'].'</button>
                </div>                                      
                </div>                      
                ';
                array_push($final_result,$v);
            }
            else{
                $v= '
                <div class="flex flex-column section-userWorkDisplay-Box">
                <div class="flex flex-row section-uploaderInfo">
                <a href="user-profile.html?userid='.$output_each['user_id'].'" class="flex flex-row flex_center_align_horizontal">
                <img src="uploads/images/'.$output_each['avatar'].'">
                <h4>'.$output_each['user_name'].'</h4>
                </a>
                <p>'.$output_each['upload_time'].'</p>
                </div>
                <div class="flex flex-column section-workdisplay">
                <a href="detail.html?post='.$output_each['post_id'].'">
                <p>'.$output_each['description'].'</p>
                </a>
                </div>      
                <div class="flex flex-row section-workInteractButtons">
                <button><img src="img/icon-like.svg">'.$output_each['collec_num'].'</button>
                <button><img src="img/icon-comment.svg">'.$output_each['comment_num'].'</button>
                </div>                                      
                </div>                      
                ';    
                array_push($final_result,$v);                  
            }
        }   
        //return the array $final_result
        return $final_result;     
    }


    function generatePostDetail($array){
        $output1=$array;
        $v1='';
        if($output1['category']==1){
            $v1= '
            <section class="section-detail-mainContent">
            <section class="picture">
            <img src="uploads/images/'.$output1['image_content'].'">
            </section>
            <section class="description">
            <img src="img/liked.png">
            <label>'.$output1['collec_num'].'</label>
            <img src="img/comment.png">
            <label>'.$output1['comment_num'].'</label>
            <label class="view">13452 views</label>
            <section class="tags">
            </section>
            <p>'.$output1['description'].'</p>
            <br><img src="uploads/images/'.$output1['avatar'].'">
            <label class="username"><a href="user-profile.html?userid='.$output1['user_id'].'">'.$output1['user_name'].'</a></label>
            <label class="userid"> @'.$output1['user_id'].'</label>
            <button id="button-index-Following" class="selected" type="follow" value="follow"><strong>Follow</strong></button>
            </section>
            </section>                      
            ';          
        }
        else{
            $v1= '
            <section class="section-detail-mainContent">
            <section class="picture">
            <p>'.$output1['description'].'</p>
            </section>
            <section class="description">
            <img src="img/liked.png">
            <label>'.$output1['collec_num'].'</label>
            <img src="img/comment.png">
            <label>'.$output1['comment_num'].'</label>
            <label class="view">13452 views</label>
            <section class="tags">
            </section>
            <br><img src="uploads/images/'.$output1['avatar'].'">
            <label class="username"><a href="user-profile.html?userid='.$output1['user_id'].'">'.$output1['user_name'].'</a></label>
            <label class="userid"> @'.$output1['user_id'].'</label>
            <button id="button-index-Following" class="selected" type="follow" value="follow"><strong>Follow</strong></button>
            </section>
            </section>                      
            ';             
        }
        //return the array $final_result
        return $v1;     
    }


    function page_cssLink($css_name){
        $cssLink = "<link rel = \"stylesheet\" href = \"". $css_name . "\">";
        echo $cssLink;
    }

    //remove/add https
    function removeHTTPS(){
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'){
            header('Location:http://'. $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']);
            exit();
        }        
    }
    function addHTTPS(){
        if($_SERVER['HTTPS']!='on'){
            header('Location:https://'. $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']);
            exit();
        }        
    }    
    ////////////////////


//old codes from other projects, may become useful reference////////////////////////////////////////////
    // function create_header(){
    //     session_start();
    //     echo'
    //         <html lang="en">
    //           <head>
    //             <title>IAT352-A3</title>
    //           </head>
    //     ';
    // }

    function create_nav(){

    	echo '	
	    <header>
			<nav class="flex">
				<div class="flex flex-row">
					<a href="index.php">Classic Models</a>
					<a href="showmodels.php">All Models</a>
					<a href="watchlist.php">Watchlist</a>
				</div>
				<div class="flex flex-row">';
                //check whether display login or logout
                if(isset($_SESSION['user'])){
				    echo '<p>Hello, ' . $_SESSION['user'] . '</p>';
                    echo '<a href="logout.php">Log out</a>'	;
                }
                else{
                    echo '<a href="login.php">Log in</a>';
                }

		echo '	</div>
			</nav>
		</header>
		';
        removeHTTPS();
    }
    function create_nav_login(){
        echo '  
        <header>
            <nav class="flex">
                <div class="flex flex-row">
                    <a href="index.php">Classic Models</a>
                    <a href="showmodels.php">All Models</a>
                    <a href="watchlist.php">Watchlist</a>
                </div>

            </nav>
        </header>
        ';
        addHTTPS();       
    }
//check if an item exists in a watchlist
function checkWatchlist($db, $product_code, $session){
    $noDuplicate=false;
    $query_checkWatchlist = "SELECT userid, productCode FROM watchlist WHERE userid='".$session."' AND productCode='".$product_code."'";
    $result_checkWatchlist=$db->query($query_checkWatchlist);
    if($result_checkWatchlist->num_rows==0){
        $noDuplicate=true;
    }    
    return $noDuplicate;
}

?>