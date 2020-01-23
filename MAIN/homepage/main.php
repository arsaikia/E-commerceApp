<?php session_start();

  include 'queries.php';

  $store = "";
  $defaultStore = "1";
  $cid = "NA";
  $storeName = "";
  $allStoresTable = "";
  $allProductCards = "";
  $categoryArray = array();
  $allProductCategories = "";
  $allProducts = array();
  $itemsInCart = 0;

  if($_SESSION["defaultStore"] != ""){
    $defaultStore = $_SESSION["defaultStore"];
  }
  else{
    $_SESSION["defaultStore"] = $defaultStore;
  }

  if ($_SESSION["cid"] !="NA"){
    $cid=$_SESSION["cid"];
  }
  else{

    if($_SESSION["cart"]){
      $itemsInCart = count($_SESSION["cart"]);
    }
    else{
      $_SESSION["cart"] = array();
    }
    
  }

  //connect to db
  $orcl = OCILogon($_SESSION["db_user"], $_SESSION["db_password"] , $_SESSION["db"]);

  if ($orcl){

      $query = oci_parse($orcl, $query5);

      oci_execute($query);

      while(oci_fetch($query)){
        $storeId = oci_result($query, "STORE_ID");
        $strName = oci_result($query, "STORE_NAME").", ".oci_result($query, "REGION").", ".oci_result($query, "ZIP");

        $allStoresTable = $allStoresTable."<tr><td><a href=\"selectStore.php/?store=".$storeId."\" id='".$storeId."'>".$strName."</a></td></tr>";

        if ($storeId==$defaultStore){
          $storeName = $strName;
          $_SESSION["storeName"] = $storeName;
        }
      }

      $allStoresTable = "<table class='table table-bordered table-striped mb-0'><tbody>".$allStoresTable."</tbody></table>";
      $query = $query2;
      if($_POST){
          $query = $query." and lower(list_name) like '%";
          $searchQuery = explode(" ", $_POST["search_query"]);
          foreach($searchQuery as $k=>$v){
            if ($v!=" "){
                $query = $query.strtolower($v)."%";
            }
          }
          $query = $query."'";

      }
      $query = oci_parse($orcl, $query);

      oci_bind_by_name($query, ":storeId", $defaultStore);

      oci_execute($query);
      
      // base array contains the most in depth category of the products
      $baseArray = array();
      while(oci_fetch($query)){
        $listName = oci_result($query, "LIST_NAME");
        $pId = oci_result($query, "P_ID");
        $productPrice = oci_result($query, "PRODUCT_PRICE");
        $quantity = oci_result($query, "QUANTITY");
        $manufacturerId = oci_result($query, "MANUFACTURER_ID");
        $categoryId = oci_result($query, "CATEGORY_ID");
        $imageName = oci_result($query, "IMAGE_NAME");
        // this creates the dynamic html products
        $allProductCards = $allProductCards."<div id='".$pId."'class=\"card col-lg-3\"><img class=\"card-img-top\" src=\"../pictures/".$imageName."\" alt=\"Card image cap\"><div class=\"card-body\"><h5 class=\"card-title\">".$listName."</h5><p class=\"card-text\">Items in stock: ".$quantity."</p><p>Unit price: &#36;".$productPrice."</p></div><div class=\"card-body\"><button id=\"add_cart_button\"type=\"button\" class=\"btn btn-primary\" value='".$pId."'>Add to cart</button></div></div>";
        $baseArray[$categoryId] = $pId;
        $allProducts[$pId] = array();
        $allProducts[$pId]["category"] = $categoryId;
      }
      // from baseArray we form the maincategories and its subDivisions
      foreach($baseArray as $cat => $pid){
        $query = oci_parse($orcl, $query4);
        oci_bind_by_name($query, ":categoryId", $cat);
        oci_execute($query);
        $currentCategories = array();
        $length = 0;

        while (oci_fetch($query)){
          $currentCategories[$length] = oci_result($query, "CATEGORY_NAME");
          $length = $length+1;
        }

        $catMain = $currentCategories[$length-1];
        foreach($allProducts as $prodid => $arr){
          if($arr["category"]==$cat){
            $allProducts[$prodid]["parent"] = $currentCategories[1];
            $allProducts[$prodid]["category"] = $currentCategories[0];
          }
        }
        for($i=$length-1;$i>=0;$i--){
          if($i+1==$length){
            if($categoryArray[$currentCategories[$i]]){
              continue;
            }
            else{
              $categoryArray[$currentCategories[$i]] = array();
            }
          }
          else{
            $categoryArray[$catMain][$currentCategories[$i]] = 1;
          }
        }

      }
      foreach($allProducts as $pid => $array){
        $allProducts[$pid] = json_encode($array);
      }
      $allProducts = json_encode($allProducts);
      $_SESSION["allProducts"] = $allProducts;

      //now form the filter div elements
      foreach($categoryArray as $cat => $subCat){
        $allProductCategories = $allProductCategories."<p class='font-weight-bold text-left text-capitalize'><input id = \"cat\" type=\"checkbox\" name='".$cat."'><span id='cat_span'>".$cat."</span></input></p>";

        foreach($subCat as $subCatName => $val){
          $allProductCategories = $allProductCategories."<p id= 'sub_cat_p' class='font-weight-normal text-capitalize'><input id = \"sub_cat\" type=\"checkbox\" name='".$subCatName.'--'.$cat."'><span id='cat_span'>".$subCatName."</span></input></p>";
        }
      }
      
      if($cid !="NA"){
        $query = oci_parse($orcl, $query7);
        oci_bind_by_name($query, ":cid", $cid);

        oci_execute($query);

        while(oci_fetch($query)){
          $itemsInCart = oci_result($query, "NUM_ITEMS");
        }
      }

      //$allProductCategories = "<form method='post' id='filter_form'>".$allProductCategories."</form>";

      OCILogoff($orcl);
  }

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style type="text/css">

    #store_info{
      margin: 8px auto;
    }
    
    #filter_panel{
      margin-left:0px;
      float:left;
      border: 2px solid black;
      height:100%;
      width:20%;
      padding:20px;
      display:none;
    }
    
    #product_card_rows{
      width:100%;
    }
    #filer_button_panel{
      height:40px;
      width:100%;
      border-bottom:0px solid black;
    }
    #filter_button{
      float:right;
      height:40px;
      border-top-right-radius:0%;
      border-bottom-right-radius:0%;
    }
    #product_container{
      display:inline-block;
      padding:5px;
    }
    #products{
      float:left;
      
    }
    #sub_cat_p{
      padding-left:20px;
    }

    #cat_span{
      padding-left:5px;
    }

    #cart{
      margin:auto 10px;
    }
    body{
       margin:0px;
       padding:0px;
     }


    </style>



    <title>B-Buy.com</title>
  </head>
  <body>
   <p id="product_json" hidden><?php echo $allProducts; ?></p>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
      <a class="navbar-brand" href="main.php">B-Buy</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="#">Products <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Brands</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Account
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="myOrders.php">My Orders</a>
              <a class="dropdown-item" href="#">Account Details</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#">Addresses</a>
              <a class="dropdown-item" href="#">Cards</a>
            </div>
          </li>
        </ul>
        <p id="store_info">Store: <a href = "#storeModal" data-toggle="modal" data-target="#storeModal"><?php echo $storeName; ?></a></p>
        <button id="cart" type="button" class="btn btn-secondary">
            Shopping Cart <span id = "num_items"class="badge badge-light"><?php echo $itemsInCart; ?></span>
        </button>
        <form class="form-inline my-2 my-lg-0" method='post'>
          <input name = 'search_query' class="form-control mr-sm-2" type="search" placeholder="Search B-Buy" aria-label="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
      </div>
    </nav>
    <div id="promoCarousel" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#promoCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#promoCarousel" data-slide-to="1"></li>
      </ol>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="../pictures/carousel/carousel1.jpg" class="d-block w-100" alt="...">
        </div>
        <div class="carousel-item">
          <img src="../pictures/carousel/carousel2.jpg" class="d-block w-100" alt="...">
        </div>
      </div>
      <a class="carousel-control-prev" href="#promoCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#promoCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
  </div>
  <div id="filer_button_panel" ><button id="filter_button"type="button" class="btn btn-secondary">Filter Products</button></div> 
  <div  id="product_container" class="container-fluid">
    <div id="filter_panel" class="container"><?php echo $allProductCategories; ?></div>
    <div id="products" class="container-fluid">
      <div id ="product_card_rows" class="row">
          <?php echo $allProductCards; ?>
      </div>
  </div>
  </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    
    
    <script type="text/javascript">

      $('button').click(function() {
        if($(this).attr('id')=='add_cart_button'){
        var pId = $(this).val();
          $.ajax({
              url: 'addToCart.php',
              type: 'POST',
              data: {
                  pid: pId,
              },
              success: function(msg) {
                  var message = JSON.parse(msg);
                  if(message.val == "1"){
                      $items = $("#num_items").text();
                      $("#num_items").text(Number($items)+1);
                  }
              },
              datatype:"text"               
          });
        }
      });

      $('#cart').click(function(){
        window.location.href='../cart/cart.php';
      });


      var productJson = $("#product_json").text();
      productJson = JSON.parse(productJson);

      
      $("#filter_button").click(function(){
        let display = $("#filter_panel").css("display");

        if (display=="none"){
          $("#filter_panel").css("display", "inline-block");
          $("#products").css("width", "80%");
        }
        else{
          $("#filter_panel").css("display", "none");
          $("#products").css("width", "100%");
        }
      });

      $("input[type=checkbox]").change(function(){

        if($(this).attr("id")=="cat"){

          var clicked_cat = this;

          let cat_name = $(clicked_cat).attr("name").toLowerCase();

          if (clicked_cat.checked){
            $('#product_card_rows').children('div').each(function(){
              const id = $(this).attr('id');

              if (typeof id !== typeof undefined && id !== false){

                if (JSON.parse(productJson[id]).parent == cat_name){ 
                  $(this).css("display", "inline-block");
                }
                else{
                  $(this).css("display", "none");
                }
              }
            });
          }
          else{
            $('#product_card_rows').children('div').each(function(){
              const id = $(this).attr('id');
              if (JSON.parse(productJson[id]).parent == cat_name){ 
                $(this).hide();
              }
              else{
                $(this).show();
              }
            });
          }
        }
        else if($(this).attr("id")=="sub_cat"){
          var clicked_cat = this;

          let cat_name = ($(clicked_cat).attr("name").toLowerCase()).split("--")[0];
          
          if (clicked_cat.checked){
            $('#product_card_rows').children('div').each(function(){
              const id = $(this).attr('id');

              if (typeof id !== typeof undefined && id !== false){

                if (JSON.parse(productJson[id]).category == cat_name){ 
                  $(this).css("display", "inline-block");
                }
                else{
                  $(this).css("display", "none");
                }
              }
            });
          }
          else{
            $('#product_card_rows').children('div').each(function(){
              const id = $(this).attr('id');
              if (JSON.parse(productJson[id]).category == cat_name){ 
                $(this).hide();
              }
              else{
                $(this).show();
              }
            });
          }
        }
      });
    </script>

<div class="modal fade" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="storeModalTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Select your store</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div id = "allStores" class="modal-body">
              <?php echo $allStoresTable; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
              </div>
            </div>
          </div>
        </div>
  </body>
</html>