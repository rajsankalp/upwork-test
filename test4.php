<!DOCTYPE html>
<html lang="en">
<head>
  <title>Test Project</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>Test Project</h2>
  <form action="" method="POST" enctype="multipart/form-data">
  
  
    <div class="form-group">
      <label for="col">Upload Recipes Collection</label>
      <input type="file" class="form-control" id="col" placeholder="" name="col">
    </div>
	
    <div class="form-group">
      <label for="r">Fridge Items</label>
      <input type="file" class="form-control" id="r" placeholder="" name="r">
    </div>


	
	
    <button name="sub" type="submit" class="btn btn-default">Submit</button>
  </form>
  
  <br/><br/><br/>
  
  
<?php 


if(isset($_POST['sub'])){
	
	$target_file = $_SERVER['DOCUMENT_ROOT'].'/public/files/recipe.csv';
	
	
    if (move_uploaded_file($_FILES["r"]["tmp_name"], $target_file)) {
        
		$uploadOk = 1;
    } else {
        echo "Sorry, there was an error uploading your file.";
		$uploadOk = 0;
    }
	
	
	$target_file2 = $_SERVER['DOCUMENT_ROOT'].'/public/files/collections.json';
	
	
    if (move_uploaded_file($_FILES["col"]["tmp_name"], $target_file2)) {
        
		$uploadOk2 = 1;
    } else {
        echo "Sorry, there was an error uploading your file.";
		$uploadOk2 = 0;
    }	

	
	if($uploadOk == 1 && $uploadOk2==1){
		
	
	
		
		$itemsSorted = [];
		$onlyItems = [];
		
		if (($handle = fopen($target_file, "r")) !== FALSE) {
			$row = 0;
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {	
				$row++;
				if($row==1) {continue;}
				
				// Skip the expired item
				if(time() > strtotime($data[3])) { continue; }
				
				$trimmedName = strtolower(trim(str_replace(' ', '', $data[0])));
				
				$itemsSorted[$trimmedName] = strtotime($data[3]);
				$onlyItems[] = $trimmedName;
				
				
			}
			fclose($handle);
		}



		$recipes = json_decode(file_get_contents($target_file2));
		
		
		$match_found = [];
		

		
		foreach($recipes as $recepe){
			$returndata = SearchFridgeItems($recepe,$itemsSorted,$onlyItems);
			$key = key($returndata);
			if($returndata){
				$match_found[$key] = $returndata[$key];
			}
		}
		
		if(count($match_found)==0){
			echo "Order Takeout";
		}else{
			
			// sort to have the recipes which containing an item with closed date 
			asort($match_found);
			
			$highlyRecommendedRecipe = end($match_found);
			

			
			echo "You are recomended to cook <strong>".$highlyRecommendedRecipe->name."</strong> tonight!";

		}
		
	}

	
}



function SearchFridgeItems($recepe,$itemsSorted,$onlyItems){



		if(count($recepe->ingredients)==0) return 0;
		
		$totalIng = count($recepe->ingredients);
		
		$countMatch = 0;
		
		$closestExpiryItem = 0;
		$returnMatch = []; 

		foreach($recepe->ingredients as $ing){
			$trimmedName = strtolower(trim(str_replace(' ', '', $ing->item)));

			
			if(in_array($trimmedName,$onlyItems)){
				$countMatch++;
				
				if($closestExpiryItem <= $itemsSorted[$trimmedName]){
					$closestExpiryItem = $itemsSorted[$trimmedName];
				}
			}
			
		}

		$returnMatch[$closestExpiryItem] = $recepe;
		
		if($countMatch == $totalIng){
			return $returnMatch;
		}
		
		return ;
	
	
}














?>  
  
  
  
  
  
  
  
  
</div>

</body>
</html>



