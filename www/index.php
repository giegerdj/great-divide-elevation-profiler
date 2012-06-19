<?php 
require_once('../esrgd-includes/php/config.php');
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" /> 
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<meta name="Author" content="Dave Gieger" />
	<meta name="description" content="Draw elevation profiles for Adventure Cycling's Great Divide Mountain Bike Route. " />
	<meta name="keywords" content="great divide elevation profile gdmbr adventure cycling aca bikepacking" />
	
	<title>Eat. Sleep. Ride. Great Divide. - Interactive GDMBR Elevation Profile</title> 

	<link href="http://code.google.com/apis/maps/documentation/javascript/examples/default.css" rel="stylesheet" type="text/css" /> 
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAA7O0MztpGlV3Tb4EO9WnNDRS057IBlI8yJGBdnTNE5Z1m9CXhnxSTjWnhUpib8SQOcgNR2JtGLSjqLA" type="text/javascript"></script>

	<link href="min/?g=css<?php echo ((SERVER == DEV_SERVER) ? '&debug=1' : ''); ?>" rel="stylesheet" type="text/css" />
	<script src="min/?g=js-init<?php echo ((SERVER == DEV_SERVER) ? '&debug=1' : ''); ?>" type="text/javascript"></script>
	<!--<script src="min/?g=js" type="text/javascript"></script>-->
<?php include('../esrgd-includes/php/static-analytics.php'); ?>
</head>
<body onload="initialize();" onunload="GUnload();">
	<div id="map_canvas"></div>
	<div id="container">
		<div id="map-menu">
			<h1 id="site-title">
				Eat. Sleep. Ride.<br />Great Divide.
			</h1>
			<ul id="site-navigation">
				<li>
					<a href="#/riders" rel="riders" class="nav-item">Current Riders</a>
					<div id="riders-nav" class="hide"></div>
				</li>
				<li>
					<a href="#/elevation" rel="elevation" class="nav-item">Elevation</a>
					<div id="elevation-nav" class="hide">
						<b>Select &amp; drag the markers on the map to see the elevation profile.</b><br />
						<input type="radio" name="direction" id="sobo" checked /> 
						<label for="sobo">North to South</label><br/> 
						<input type="radio" name="direction" id="nobo" /> 
						<label for="nobo">South to North</label>
					</div>
				</li>
				<li>
					<a href="#/about" rel="riders" class="nav-item">Learn More</a>
					<div id="about-nav" class="hide"></div>
				</li>
			</ul>
			<div id="elevation-stats" class="hide"></div>
		</div>
		<div id="content" class="hide">
		<!-- BEGIN: content-container -->
			<ul id="primary-content-stages">
				<li class="primary-content-stage" rel="about">
					<div id="content-container">
						<div id="content-main">
							<h2>What is ESRGD?</h2>
							<h4>
								A place for nostalgic veterans, armchair superfans, and current contenders to see
								 who's on the Divide.
							</h4>
							<div class="about-holder">
								<b>Current Riders</b>
								<p>
									If you're carrying a <a href="http://findmespot.com/">SPOT tracker</a>, we'll show you 
									on the map with all other riders. Add your blog info to let others follow your journey.
									Your path will be shown along with an elevation profile and climbing statistics.  
									<a href="#/riders/signup">Sign up</a> or <a href="#/riders/login">log in</a> to get started.
								</p>
								<b>Veterans and Superfans</b>
								<p>
									Relive your adventures through others who are fortunate enough to be 
									on the Divide.  Who doesn't wish they were out there right now?
								</p>
								<br />
								<b>Due Dilligence</b>
								<p>
									Thanks to <a href="http://adventurecycling.org">Adventure Cycling</a> for 
									the amazing route and Scott Morris at 
									<a href="http://topofusion.com">TopoFusion</a> for the 
									coordinate/elevation data.<br /><br />App developed by Dave Gieger 
									(<a href="http://gdmbr.davegieger.com">2010 trip journal</a>).  
									Email me at dave&lt;at&gt;eatsleepridegreatdivide.com
								</p>
							</div>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="add-spot">
					<div id="content-container">
						<div id="content-main">
							<h2>Add Your SPOT</h2>
							<h4>Before you can add your SPOT to the map, you need to <a href="#/riders/login">log in</a> or <a href="#/riders/signup">sign up</a>.</h4>
							<div class="spot-edit-holder">
								Once you add your info, you'll show up on the map like this:<br />
								<img src="images/screen.png" style="display:block;margin: 0px auto;">
							</div>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="edit-spot">
					<div id="content-container">
						<div id="content-main">
							<h2>SPOT Info</h2>
							<h4>Add your SPOT and blog info and you'll show up on the map</h4>
							<div class="form-success hide"></div>
							<div class="form-errors hide"></div>
							<form class="spot-edit-holder" id="spot-edit-holder">
								<table>
									<tr>
										<td colspan="2">Title</td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="text" name="title" id="title" class="long-input-text" />
										</td>
									</tr>
									<tr>
										<td colspan="2"><br>SPOT ID</td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="text" name="spot-url" id="spot-url" class="long-input-text" />
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<font style="font-weight:normal;">http://share.findmespot.com/shared/faces/viewspots.jsp?<br>glId=</font><u>0S1OKj0yxn5MGVG23X6qPaFXyM5b1PQs2</u>
										</td>
									</tr>
									<tr>
										<td colspan="2"><br>Logistics</td>
									</tr>
									<tr>
										<td>
											<select id="ride-dir" name="ride-dir">
												<option>Direction:</option>
												<option value="S">Southbound</option>
												<option value="N">Northbound</option>
											</select>
										</td>
										<td>
											<select id="ride-type" name="ride-type">
												<option>Ride Type:</option>
												<option value="Slow Tour">Slow Tour</option>
												<option value="Tour Divide">Tour Divide</option>
												<option value="Great Divide Race">Great Divide Race</option>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2"><br />Start Date (we'll ignore SPOT coordinates before this date)</td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="text" name="date" id="date" class="long-input-text" />
										</td>
									</tr>
									<tr>
										<td colspan="2"><br />Links (blogs, photo albums, etc.)</td>
									</tr>
									<tr>
										<td colspan="2">
											<div id="links"></div>
										</td>
									</tr>
									<tr>
										<td colspan="2"><a href="#" id="add-link">+ add another</a></td>
									</tr>
									<tr>
										<td colspan="2" style="text-align:right;padding: 5px 0px;">
											<button type="submit" name="save-button" class="save-button">Save SPOT</button><br /><br />
											 or <a href="#/riders/delete" class="delete delete-button" id="delete-link">delete</a>
										</td>
									</tr>
								</table>
							</form>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="login">
					<div id="content-container">
						<div id="content-main">
							<h2>Log In</h2>
							<h4>Log in to edit your SPOT and blog info</h4>
							<div class="form-errors hide"></div>
							<form id="login-holder" class="login-holder">
								<table>
									<tr>
										<td>Email</td>
									</tr>
									<tr>
										<td><input type="text" name="email" /></td>
									</tr>
									<tr>
										<td>Password</td>
									</tr>
									<tr>
										<td><input type="password" name="password" /></td>
									</tr>
									<tr>
										<td style="text-align:right;padding: 5px 0px;">
											<button type="submit" class="login-button" name="login-button">Log In</button>
											<br /><br />or <a href="#/riders/signup">sign up</a>
										</td>
									</tr>
								</table>
							</form>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="signup">
					<div id="content-container">
						<div id="content-main">
							<h2>Sign Up</h2>
							<h4>Sign up if you want your Great Divide SPOT and blog to show on the map</h4>
							<div class="form-errors hide"></div>
							<form id="signup-holder" class="signup-holder">
								<table>
									<tr>
										<td>Email</td>
									</tr>
									<tr>
										<td><input type="text" name="email" /></td>
									</tr>
									<tr>
										<td>Password</td>
									</tr>
									<tr>
										<td><input type="password" name="spassword" id="spassword" /></td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" id="verify-pw" /> <label for="verify-pw">verify password</label>
										</td>
									</tr>
									<tr>
										<td style="text-align:right;padding: 5px 0px;">
											<button type="submit" class="signup-button" name="signup-button">Sign Up</button>
											<br /><br />or <a href="#/riders/login">log in</a>
										</td>
									</tr>
								</table>
							</form>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="settings">
					<div id="content-container">
						<div id="content-main">
							<h2>My Settings</h2>
							<h4>Update your email address and password here.</h4>
							<div class="form-success hide"></div>
							<div class="form-errors hide"></div>
							<form class="settings-holder" id="settings-holder">
								<table>
									<tr>
										<td colspan="2">Email Address</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="email" id="email" /></td>
									</tr>
									<tr>
										<td colspan="2">New Password</td>
									</tr>
									<tr>
										<td colspan="2"><input type="password" name="sspassword" id="sspassword" /></td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" id="sverify-pw"> <label for="sverify-pw">verify password</label>
										</td>
									</tr>
									<tr>
										<td colspan="2"><br>Old Password (required to save changes)</td>
									</tr>
									<tr>
										<td colspan="2">
											<input type="password" name="oldpassword" id="oldpassword" />
										</td>
									</tr>
									<tr>
										<td colspan="2" style="text-align:right;padding: 5px 0px;">
											<button type="submit" name="save-button" class="save-button">Save Settings</button><br /><br />
											 or <a href="#/riders" class="delete delete-button">cancel</a>
										</td>
									</tr>
								</table>
							</form>
						</div>
						<div id="content-close">
							<a href="#/riders" title="close window">x</a>
						</div>
						<div class="clear"></div>
					</div>	
				</li>
				<li class="primary-content-stage" rel="reset">
					<div id="content-container">
						<div id="content-main">
							<h2>Get A Temporary Password</h2>
							<h4>We'll send it to your email address so you can log in and change your password</h4>
							<div class="form-success hide"></div>
							<div class="form-errors hide"></div>
							<form class="recover-holder" id="recover-holder">
								<table>
									<tr>
										<td>Account Email</td>
									</tr>
									<tr>
										<td><input type="text" id="email" name="email" /></td>
									</tr>
									<tr>
										<td style="text-align:right;padding: 5px 0px;">
											<button class="signup-button" name="reset-button">Send Temp Password</button>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</li>
			</ul>
			<!-- END: content-container -->
		</div>
		<div class="clear-menu"></div>
	</div>
	<div id="elevation-profile-img" class="hide">
		<img src="graph.php" />
	</div>
	<div id="no-riders" class="hide">
		<h4>There are no riders to display right now.</h4>
		If you're riding the Divide with a SPOT, <a href="#/riders/signup">sign up</a> to be 
		listed on the map.
	</div>
	<ul id="marker-data-stage" class="hide">
	</ul>
</body>
</html>


