<!DOCTYPE html> <html> <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Water Meter - Settings</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="form-labels-on-top.css"> </head>
	<header>
		<h1>Pi Water Meter Settings</h1>
        
    </header>
    
    <div class="main-content">
	<?php 
            
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            $myPDO = new PDO('sqlite:/home/pi/pimeter/mywater.db'); 
            $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (isset($_POST["hidPost"])) {
                $msg = "";
                if (!empty($_POST["txt_pulse"]) && !is_numeric($_POST["txt_pulse"])) {$msg = "pulse flow amount must be numeric.<br />";}
                
                if ($msg == "") {
                    // Save the data
                    
                    $currentDateTime = date('Y-m-d H:i:s');
                    try {
                        $sql = "UPDATE holding SET pulse = ?, flow_topic = ?, lh_topic = ?, last_updated = ?, mqtt_ip = ? WHERE rownum = ?";
                        $stmt = $myPDO->prepare($sql);
                        $stmt->execute([$_POST['txt_pulse'], $_POST['txt_flow_topic'], $_POST['txt_lh_topic'], $currentDateTime, $_POST['txt_ip'], 100]);
                        $msg = "<span style='color:green;'>The settings have been saved.<br />You must restart the container for changes to take effect.</span>";
                        } 
                    catch(PDOException $e)
                        {
                        $msg = "<span style='color: red'>Database error: " . $e->getMessage() . "</span>";
                        }
                        
                }
                else{
                    // display validation error
                    $msg = "<span style='color:red;'>Your settings were not saved due to the following issue(s):</span><br />".$msg;
                }
                
                }
            else {
                $msg = "";
            }
            // get data to display
            
            $stmt = $myPDO->prepare("SELECT * FROM holding WHERE rownum = 100");
            $stmt->execute();
            $arr = $stmt->fetch();
            $stmt = null; 
            print "<div style='text-align: center;padding: 12px 2px;'>".$msg."</div>";
	 ?>
        
        <!-- You only need this form and the form-labels-on-top.css -->
        <form class="form-labels-on-top" method="post" action="#">
            <div class="form-title-row">
                <h1>Meter Settings</h1>

            </div>
            <h3>Last updated: <?php print $arr['last_updated']; ?></h3>
            <div class="form-row">
                <label>
                    <span>Flow Per Pulse (ounces)</span>
                    <input type="text" name="txt_pulse" value="<?php print $arr['pulse']; ?>">
                </label>
            </div>
            
            <div class="form-row">
                <h2>MQTT Settings</h2>
            </div>
            <div class="form-row">
                <label>
                    <span>Broker IP Address</span>
                    <input type="text" name="txt_ip" value="<?php print $arr['mqtt_ip']; ?>">
                </label>
            </div>
            <div class="form-row">
                <label>
                    <span>MQTT Flow Topic</span>
                    <input type="text" name="txt_flow_topic" value="<?php print $arr['flow_topic']; ?>">
                </label>
            </div>
            <div class="form-row">
                <label>
                    <span>MQTT Last Hour Topic</span>
                    <input type="text" name="txt_lh_topic" value="<?php print $arr['lh_topic']; ?>">
                </label>
            </div>

            
            
            <input type="hidden" name="hidPost" value="yes">
            <div class="form-row">
                <button type="submit">Submit Form</button>
            </div>
        </form>
    </div> </body>
</html>
<?php
    $myPDO = null;
?>
