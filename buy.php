<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');
$basketListstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=####&visitorUserAgent&visitorIPAddress&trackingId=####&categoryId=72&showAllDescendants=true');
$basketList = new SimpleXMLElement($basketListstr);
$Total=0;
if(!isset($_SESSION['basket']) || $_SESSION['itemId']==null)
{
  $_SESSION['basket']=array();
  $_SESSION['itemId']=array();
}
//insert the item in the basket
if (isset($_GET['buy'])) 
{
	$itemId=$_GET['buy'];
	$itemSrc='http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=####&visitorUserAgent&visitorIPAddress&trackingId=7000610&productId='.$itemId;
	$itemStr=file_get_contents($itemSrc);
	$itemxml=new SimpleXMLElement($itemStr);
	$itemImg=(string)$itemxml->categories->category->items->product->images->image[0]->sourceURL;
	$itemName=(string)$itemxml->categories->category->items->product->name;
	$itemPrice=(string)$itemxml->categories->category->items->product->minPrice;
	$itemOfferUrl=(string)$itemxml->categories->category->items->product->productOffersURL;
	$item_Array=array();
	array_push($item_Array,$itemId);
	array_push($item_Array,$itemImg);
	array_push($item_Array,$itemName);
	array_push($item_Array,$itemPrice);
	array_push($item_Array,$itemOfferUrl);
	if(!in_array($itemId,$_SESSION['itemId']))
	{
		$_SESSION['itemId'][$itemId]=$itemId;
		$_SESSION['basket'][$itemId]=$item_Array;
		
	}
}
//delete the item from the basket
elseif(isset($_GET['delete']))
{
	$item_delete=$_GET['delete'];
	unset($_SESSION['basket'][$item_delete]);
	unset($_SESSION['itemId'][$item_delete]);
}
//Clear the shopping cart
elseif (isset($_GET['clear'])) 
{
  session_unset();
  $_SESSION['basket']=array();
  $_SESSION['itemId']=array();
}
?>

<p>Shopping Basket:</p>

<?php
//displaying the details in the shopping cart
if(!empty($_SESSION['basket']))
{   echo "<table border=\"1\">";
	foreach($_SESSION['basket'] as $product)
	{
		if($product!='')
		{   
			echo "<tr>";
			$delete_link='buy.php?delete='.$product[0];
			echo "<td><a href='".$product[4]."'><img src=\"".$product[1]."\"></img></a></td>";
			echo "<td>".$product[2]."</td>";
			echo "<td>$".$product[3]."</td>";
			$Total=$Total+$product[3];
			echo "<td><a href='".$delete_link."'>delete</td></tr>;";
		}
	}
	echo "</table>";
}
echo "Total:$" .$Total;
?>
</table>;

<form action="buy.php" method="GET">
<input type="hidden" name="clear" value="1">
    <input type="submit" value="Empty Basket">
	
  </form>
	<form action="buy.php" method="GET">
	<fieldset>
	<legend style="width:500px,height=500px">Find product</legend>
		<label>Category:<select name="category"></label>     
<?php
//forming the drop down list
error_reporting(E_ALL);
ini_set('display_errors','On');
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=####&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);

echo "<optgroup label=".$xml->category->name.">";

echo "<option value=".$xml->category->categories->category['id'].">".$xml->category->categories->category->name."</option></optgroup>";

foreach ($xml->category->categories->{'category'} as $availabe_categories)
	{  
		
		echo "<optgroup label=".$availabe_categories->name.">";
		foreach ($availabe_categories->categories->{'category'} as $availabe_subcategories)
		{
		$subcat_id=$availabe_subcategories['id'];
		echo "<option value=".$subcat_id.">".$availabe_subcategories->name."</option>";//try to get the category id
		}
		echo "</optgroup>";
	}

?>
</select>
<label>Search Keywords:<input type="text" name="keyword"/></label>
       <input type="submit"  value="Search" />
	   
</fieldset>
</form>
<table border="1">;
<?php
	if(isset($_GET['keyword'])&&($_GET['category']))
	{
		$categoryid=$_GET['category'];
		$keywordid=$_GET['keyword'];
		//$keywordurl=urlencode($keywordid);
		$keywordurl=str_replace(' ','+',$keywordid);
		$detailstr = file_get_contents('http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=####&trackingId=7000610&categoryId='.$categoryid.'&keyword='.$keywordurl."&numItems=20");
		$detailxml = new SimpleXMLElement($detailstr);
		foreach ($detailxml->categories->category->{'items'} as $availabe_items){	
			foreach($availabe_items->product as $available_categories)
			{
				$offer_link='buy.php?buy='.$available_categories['id'];
				echo "<tr>";
				echo "<td><a href='".$offer_link."'><img src=\"".$available_categories->images->image->sourceURL."\"></a></td>";
				echo "<td>".$available_categories->name."</td>"; 
				echo "<td>$".$available_categories->minPrice."</td>";
				echo "<td>" .$available_categories->fullDescription."</td></tr>";
			}
		}

	}
?>
</table>;
</body>
</html>