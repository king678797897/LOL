<?php
$API_URL = "https://github.com/king678797897/LOL/edit/main/index.php";
$API_KEY = "demo";

$file = "api_usage.json";
if(!file_exists($file)){
    file_put_contents($file, json_encode(["total"=>0]));
}
$usage = json_decode(file_get_contents($file), true);

if(isset($_GET["num"])){
    $num = preg_replace('/[^0-9]/','',$_GET["num"]);
    header("Content-Type: application/json");
    if(strlen($num) !== 10){
        echo json_encode(["error"=>"Invalid number"]);
        exit;
    }
    $usage["total"] = $usage["total"] + 1;
    file_put_contents($file, json_encode($usage));
    $url = $API_URL."?key=".$API_KEY."&num=".$num;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Mobile Tracker - ANISH EXPLOITS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:white;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;padding:20px;}
.glass-box{max-width:750px;background:white;margin:20px auto;padding:30px;border-radius:20px;border:1px solid #ddd;}
.header{text-align:center;margin-bottom:30px;}
.header h2{color:black;font-size:2.2em;margin-bottom:10px;font-weight:700;}
.header p{color:#444;font-size:1.1em;}
.input-group{margin-bottom:25px;}
.input-group input{width:100%;padding:18px 20px;background:white;border:2px solid #bbb;border-radius:15px;font-size:18px;color:black;}
.input-group input::placeholder{color:#777;}
.glow-button{width:100%;padding:18px;background:black;border:none;border-radius:15px;color:white;font-size:18px;cursor:pointer;}
.loader{display:none;margin:25px auto;width:60px;height:60px;border:4px solid #ccc;border-top:4px solid black;border-radius:50%;animation:spin 1s linear infinite;}
@keyframes spin{100%{transform:rotate(360deg);}}
#info{white-space:pre-wrap;margin-top:25px;font-size:15px;background:white;padding:25px;border-radius:15px;display:none;color:black;border:1px solid #ddd;}
#map{height:520px;border-radius:20px;margin-top:25px;display:none;border:2px solid #ccc;overflow:hidden;position:relative;}
.map-overlay{position:absolute;top:15px;left:15px;z-index:1000;background:black;color:white;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;}
.distance-box{position:absolute;bottom:15px;left:15px;z-index:1000;background:black;color:white;padding:12px 15px;border-radius:12px;font-size:14px;font-weight:600;}
.map-type-btn{position:absolute;top:15px;right:15px;z-index:1000;background:black;color:white;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;border:none;}
.map-controls{position:absolute;top:60px;right:15px;z-index:1000;background:white;padding:15px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.2);min-width:200px;display:none;}
.control-group{margin:10px 0;}
.control-group strong{display:block;margin-bottom:8px;color:#333;}
.control-group label{display:block;margin:5px 0;cursor:pointer;font-size:14px;color:#555;}
.control-group input{margin-right:8px;}
.pulse-marker{background:radial-gradient(circle, red 0%, rgba(255,0,0,0) 70%);border-radius:50%;animation:pulse 2s infinite;}
@keyframes pulse{0%{transform:scale(0.8);opacity:1;}70%{transform:scale(2);opacity:0;}100%{transform:scale(2);opacity:0;}}
.location-pin{background:red;width:24px;height:24px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);position:relative;}
.location-pin::after{content:'';width:12px;height:12px;background:white;border-radius:50%;position:absolute;top:6px;left:6px;}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>

<div id="atomicPopup" style="position:fixed;top:0;left:0;width:100%;height:100%;background:white;color:black;display:flex;justify-content:center;align-items:center;text-align:center;font-size:18px;padding:20px;z-index:99999;border:2px solid black;">
<div>
<h2 style="margin-bottom:15px;color:black;">⚠️ Disclaimer</h2>
<p style="color:black;">This website is created only for educational purposes. All features shown are for demo, testing, and security simulation only. This is a simulator used to test and improve system security. Any misuse or illegal use is strictly prohibited.</p>
</div>
</div>
<script>
setTimeout(function(){document.getElementById("atomicPopup").style.display="none";},3000);
</script>

<div class="glass-box">
<div class="header">
<h2>Exploits Education Tracker</h2>
<p>Powered by ANISH EXPLOITS</p>
</div>
<div class="input-group">
<input id="number" placeholder="Enter 10 digit mobile number...">
</div>
<button class="glow-button" onclick="startTrack()">Check Now</button>
<div id="loader" class="loader"></div>
<div id="info"></div>
<div id="map">
<div class="map-overlay">LIVE GPS MAP</div>
<button class="map-type-btn" onclick="toggleMapControls()">Map Type</button>
<div class="map-controls" id="mapControls">
<div class="control-group">
<strong>Map Type</strong>
<label><input type="radio" name="maptype" value="default" checked> Default</label>
<label><input type="radio" name="maptype" value="satellite"> Satellite</label>
<label><input type="radio" name="maptype" value="terrain"> Terrain</label>
</div>
</div>
<div class="distance-box" id="distanceBox">Distance: -- KM</div>
</div>
</div>

<script>
let targetNumber="",map=null,marker=null,circle=null,userMarker=null,routeLine=null,currentTileLayer=null;
function startTrack(){
let num=document.getElementById("number").value;
if(num.length!==10||isNaN(num)){alert("Enter valid 10 digit number");return;}
targetNumber=num;
document.getElementById("loader").style.display="block";
document.getElementById("info").style.display="none";
document.getElementById("map").style.display="none";
if(map){map.remove();map=null;}
fetch("?num="+num).then(r=>r.json()).then(api=>{
document.getElementById("loader").style.display="none";
if(api.result&&api.result[0]){
let info=api.result[0];
showInfo(info);
document.getElementById("map").style.display="block";
let pin=getPincode(info.address);
setTimeout(()=>geo(pin,info.address),800);
}});
}
function toggleMapControls(){
const c=document.getElementById('mapControls');
c.style.display=c.style.display==='block'?'none':'block';
}
function getPincode(a){
let m=a.match(/\b\d{6}\b/);
return m?m[0]:null;
}
function showInfo(x){
let p=getPincode(x.address);
let pinText=p?`📍 Pincode: ${p}`:"";
let t=`📱 INFORMATION FOUND

🎯 Target: ${targetNumber}

👤 Name: ${x.name}
👨‍👦 Father Name: ${x.father_name}
📞 Mobile: ${x.mobile}
🆔 Aadhar: ${x.id_number}
🏡 Address: ${x.address}
${pinText}
📞 Alternate: ${x.alt_mobile}
📍 Circle: ${x.circle}
🪪 ID: ${x.id}

⚡ By ANISH EXPLOITS`;
document.getElementById("info").innerText=t;
document.getElementById("info").style.display="block";
}
function geo(pin,addr){
let q=pin?pin+" India":addr;
fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
.then(r=>r.json()).then(d=>{
if(d.length>0){
let lat=parseFloat(d[0].lat),lng=parseFloat(d[0].lon);
initMap(lat,lng,addr);
}});
}
function initMap(lat,lng,addr){
map=L.map('map').setView([lat,lng],14);
currentTileLayer=L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
let icon=L.divIcon({html:`<div class="pulse-marker"></div><div class="location-pin"></div>`,iconSize:[40,50],iconAnchor:[12,40]});
marker=L.marker([lat,lng],{icon:icon}).addTo(map);
circle=L.circle([lat,lng],{color:'red',fillColor:'red',fillOpacity:0.1,radius:400}).addTo(map);
marker.bindPopup(`Target Location<br>${addr.substring(0,80)}...`).openPopup();
trackUser(lat,lng);
}
function trackUser(tlat,tlng){
if(!navigator.geolocation){alert("GPS Not Supported");return;}
navigator.geolocation.watchPosition(pos=>{
let ulat=pos.coords.latitude,ulng=pos.coords.longitude;
if(userMarker){map.removeLayer(userMarker);}
userMarker=L.marker([ulat,ulng]).addTo(map);
let dist=haversine(ulat,ulng,tlat,tlng).toFixed(2);
document.getElementById("distanceBox").innerText="Distance: "+dist+" KM";
if(routeLine){map.removeLayer(routeLine);}
routeLine=L.polyline([[ulat,ulng],[tlat,tlng]],{color:"blue",weight:4}).addTo(map);
});
}
function haversine(lat1,lon1,lat2,lon2){
var R=6371;
var dLat=(lat2-lat1)*Math.PI/180;
var dLon=(lon2-lon1)*Math.PI/180;
lat1=lat1*Math.PI/180;lat2=lat2*Math.PI/180;
var a=Math.sin(dLat/2)*Math.sin(dLat/2)+Math.sin(dLon/2)*Math.sin(dLon/2)*Math.cos(lat1)*Math.cos(lat2);
var c=2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
return R*c;
}
</script>
</body>
</html>
