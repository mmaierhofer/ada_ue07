<?php
/***************************************************************************
 *                               cronlinks.php
 *                            -------------------
 *   begin                : Sunday, Aug 28, 2005
 *   copyright            : (C) 2005 Stephane DROUX
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

include ("./vogoo.php");

if ($vogoo->connected == false)
{
	die('Could not connect to DB');
}

// Get all categories
$sql = <<<EOF
SELECT DISTINCT category
FROM vogoo_ratings
EOF;

if ( !($result = $vogoo->db->sql_query2($sql)) )
{
	die('Cannot get the categories');
}

$cat = array();
//while ($row = $vogoo->db->sql_fetchrow($result))
foreach($result as $row)
{
	$cat[] = $row['category'];
}
//$vogoo->db->sql_freeresult($result);

foreach ($cat as $c)
{
	// Reset counters
	$sql = <<<EOF
UPDATE vogoo_links
SET cnt=0
WHERE category={$c}
EOF;
	$result = $vogoo->db->sql_query2($sql);
	//var_dump($result);
	//if ( !($result = $vogoo->db->sql_query2($sql)) )
	if($result === false)
	{
		die('Cannot reset the counters');
	}

	// Get all members in this category
	$sql = <<<EOF
SELECT DISTINCT member_id
FROM vogoo_ratings
WHERE category={$c}
EOF;

	if ( !($result = $vogoo->db->sql_query2($sql)) )
	{
		die('Cannot get the members');
	}

	//while ($row = $vogoo->db->sql_fetchrow($result))
	foreach($result as $row)
	{
		$member_id = $row['member_id'];
		$sql = <<<EOF
SELECT product_id,rating
FROM vogoo_ratings
WHERE member_id={$member_id} AND category={$c}
EOF;
		if ( !($res2 = $vogoo->db->sql_query2($sql)) )
		{
			die('DB error');
		}

		$items = array();
		//while ($row2 = $vogoo->db->sql_fetchrow($res2))
		foreach($res2 as $row2)
		{
			$item_id = $row2['product_id'];
			$rating = $row2['rating'];
			if ($rating >= VG_THRESHOLD_RATING)
			{
				foreach ($items as $id)
				{
					$id1 = $item_id;
					$id2 = $id;
					$sql = <<<EOF
SELECT cnt
FROM vogoo_links
WHERE item_id1={$id1} AND item_id2={$id2} AND category={$c}
EOF;
					//if ( !($res3 = $vogoo->db->sql_query2($sql)) )
					$res3 = $vogoo->db->sql_query2($sql);
					//var_dump($res3);
					if($res3 === null)
					{
						echo($sql);
						var_dump($res3);
						echo("error3");
						die('DB error');
					}
					//if ($vogoo->db->sql_numrows($res3) == 1)
					//if(sizeof($res3) == 1)
					if(! is_array($res3[0]))
					{
						$sql = <<<EOF
UPDATE vogoo_links
SET cnt=cnt+1
WHERE item_id1={$id1} AND item_id2={$id2} AND category={$c}
EOF;
						$res4 = $vogoo->db->sql_query2($sql);
						//if ( !($res4 = $vogoo->db->sql_query2($sql)) )
						if($res4 === false)
						{
							echo("error4");
							die('DB error');
						}
						$sql = <<<EOF
UPDATE vogoo_links
SET cnt=cnt+1
WHERE item_id1={$id2} AND item_id2={$id1} AND category={$c}
EOF;
						$res4 = $vogoo->db->sql_query2($sql);
						//if ( !($res4 = $vogoo->db->sql_query2($sql)) )
						if($res4 === false)
						{
							echo("error42");
							die('DB error');
						}
					}
					else
					{
						$sql = <<<EOF
INSERT INTO vogoo_links(item_id1,item_id2,category,cnt,diff_slope)
VALUES ({$id1},{$id2},{$c},1,0.0)
EOF;
						$res4 = $vogoo->db->sql_query2($sql);
						//if ( !($res4 = $vogoo->db->sql_query2($sql)) )
						if($res4 === false)
						{
							//echo($sql);
							echo("error43");
							die('DB error');
						}
						$sql = <<<EOF
INSERT INTO vogoo_links(item_id1,item_id2,category,cnt,diff_slope)
VALUES ({$id2},{$id1},{$c},1,0.0)
EOF;
						$res4 = $vogoo->db->sql_query2($sql);
						
						//if ( !($res4 = $vogoo->db->sql_query2($sql)) )
						if($res4 === false)
						{
							echo("error44");
							die('DB error');
						}
					}
					//$vogoo->db->sql_freeresult($res3);
				}
				$items[] = $item_id;
			}
		}
		//$vogoo->db->sql_freeresult($res2);
	}
	//$vogoo->db->sql_freeresult($result);
}
?>
