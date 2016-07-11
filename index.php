<?php
    session_start();
    require_once("./vendor/autoload.php");

    if(file_exists(__DIR__ . "/.env")) {
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/");
        $dotenv->load();
    }

    Braintree\Configuration::environment(getenv('BT_ENVIRONMENT'));
    Braintree\Configuration::merchantId(getenv('BT_MERCHANT_ID'));
    Braintree\Configuration::publicKey(getenv('BT_PUBLIC_KEY'));
    Braintree\Configuration::privateKey(getenv('BT_PRIVATE_KEY'));

    if (isset($_SESSION["errors"]) || isset($_SESSION["success"]))  {
        if (isset($_SESSION["errors"])) {
            $alertKlass = "alert-error";
            $alertHeader = "Transaction Error!";
            $alertMessage = $_SESSION["errors"];

            unset($_SESSION["errors"]);
        } else if ($_SESSION["success"]) {
            $transaction = Braintree\Transaction::find($_SESSION["success"]);

            $transactionSuccessStatuses = [
                Braintree\Transaction::AUTHORIZED,
                Braintree\Transaction::AUTHORIZING,
                Braintree\Transaction::SETTLED,
                Braintree\Transaction::SETTLING,
                Braintree\Transaction::SETTLEMENT_CONFIRMED,
                Braintree\Transaction::SETTLEMENT_PENDING,
                Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
            ];

            if (in_array($transaction->status, $transactionSuccessStatuses)) {
                $isSuccessful = 1;
                $alertKlass = "alert-success";
                $alertHeader = "Success!";
                $alertMessage = "Thank you for your donation.";
            } else {
                $alertKlass = "alert-error";
                $alertHeader = "Transaction Failed";
                $alertMessage = "Your transaction has a status of " . $transaction->status;
            }

            unset($_SESSION["success"]);
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>maps.stamen.com</title>
        <meta name="description" content="Stamen's toner, terrain and watercolor map styles are lovingly crafted and free for the taking.">

        <meta property="og:title" content="Stamen Maps">
        <meta property="og:type" content="website">
        <meta property="og:url" content="http://maps.stamen.com/">
        <meta property="og:description" content="Stamen's toner, terrain and watercolor map styles are lovingly crafted and free for the taking.">
        <meta property="og:image" content="http://maps.stamen.com/images/fb-watercolor.png">
        <meta property="og:image" content="http://maps.stamen.com/images/fb-toner.png">
        <meta property="og:image" content="http://maps.stamen.com/images/fb-terrain.png">

        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
        <style type="text/css">
            @import url(css/bootstrap/bootstrap.css);
            @import url(css/screen.css?white-maps);
        </style>

    </head>
    <body>
    <?php if (isset($alertKlass)): ?>
        <div class="notice-wrapper alert alert-block <?php echo($alertKlass)?>">
            <button type="button" class="close" data-dismiss="alert">&times;</button>

            <h3><?php echo($alertHeader)?></h3>
            <span class="notice-message"><?php echo($alertMessage)?></span>

            <?php if (isset($isSuccessful) && $isSuccessful): ?>
                <article>
                    <section>
                        <h4>Transaction Details</h4>
                        <table cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td><?php echo($transaction->id)?></td>
                                </tr>

                                <tr>
                                    <td>amount</td>
                                    <td>$<?php echo($transaction->amount)?></td>
                                </tr>

                                <tr>
                                    <td>status</td>
                                    <td><?php echo($transaction->status)?></td>
                                </tr>

                                <tr>
                                    <td>created_at</td>
                                    <td><?php echo($transaction->createdAt->format('Y-m-d H:i:s'))?></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </article>
            <?php endif; ?>
        </div>
    <?php endif; ?>

        <div id="header" class="navbar">
            <div class="navbar-inner">
                <div class="container">
                    <h1 class="brand">maps.stamen.com</h1>
                    <a id="stamen" class="brand" href="http://stamen.com">stamen</a>
                    <a id="toggle-feedback" class="brand toggler">report a bug</a>
                </div>
            </div>
        </div>

        <div id="hero">
            <div id="map-main" class="map switch" data-provider="toner">
                <div id="bottom">
                    <div class="container">
                        <!--<h2 id="main-title"><a id="main-permalink" class="permalink" href="#"><span class="provider-name"></span><span class="icon">&#x21F2;</span></a></h2>-->
                        <h2 id="main-title">
                            <a href="" id="main-permalink" class="permalink"><span class="provider-name"></span><span>&#x21F2;</span></a>
                        </h2>
                    </div>
                </div>
            </div>


            <div id="maps-sub" class="container">
                <form id="search" action="#">
                    <p>
                    <input id="search-location" class="span3" type="text" placeholder="Type a location">
                    <input id="search-submit" class="btn" type="submit" value="Find">
                    </p>
                </form>

                <div id="embed" class="toggle controls">
                    <a id="embed-toggle" class="toggler" title="embed this map">&lt;embed&gt;</a>
                    <a id="make-image" title="make an image of this map">&lt;image&gt;</a>
                    <div id="embed-content" class="toggle-content" style="display: none;">
                        <p>Copy the HTML below to embed this map:</p>
                        <textarea id="embed-code" rows="3">&lt;iframe width="600" height="420" src="{url}"&gt;&lt;/iframe&gt;</textarea>
                    </div>
                </div>

                <div id="controls" class="controls">
                    <a id="zoom-in" title="zoom in">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M19 13h-6v6h-2v-6h-6v-2h6v-6h2v6h6v2z"/>
                            <path d="M0 0h24v24h-24z" fill="none"/>
                        </svg>
                    </a>
                    <a id="zoom-out" title="zoom out">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M19 13h-14v-2h14v2z"/>
                            <path d="M0 0h24v24h-24z" fill="none"/>
                        </svg>
                    </a>
                </div>
                <div id="map-sub2" class="map switch" data-provider="terrain"><h3><a class="provider-name" href="#terrain">Terrain</a></h3></div>
                <div id="map-sub3" class="map switch" data-provider="watercolor"><h3><a class="provider-name" href="#watercolor">Watercolor</a></h3></div>
                <div id="feedback" class="toggle" style="display: none;">
                    <div>
                        <p><strong>Not looking right?</strong> <a href="http://citytracking.org/some-known-bugs-and-whats-to-do/">See here for more info.</a></p>
                        <p>You can help us track down problems by creating an <a href="https://github.com/stamen/maps.stamen.com/issues/new">issue</a> in the <a href="https://github.com/stamen/maps.stamen.com">maps.stamen.com</a> repo.</a></p>

                        <h4>When submitting a new issue, please:</h4>
                        <ul>
                            <li>Make sure there isn't an issue already submitted.</li>
                            <li>Include in the description:
                                <ol>
                                <li>Current maps.stamen.com URL</li>
                                <li>A detailed description of the problem</li>
                                <li>Screenshots would be great too!</li>
                                </ol>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="content" class="container content">
            <p id="desc">For over a decade, Stamen has been exploring
            <a href="http://stamen.com/cartography">cartography</a>
            with our <a href="http://stamen.com/clients">clients</a>
            and in <a href="http://stamen.com/projects">research</a>.
            These maps are presented here for your enjoyment and use wherever
            you display <a href="http://openstreetmap.org">OpenStreetMap</a>
            data.</p>
            <div class="row">
                <div id="donate" class="sidebar span3">
                    <h2>Donate</h2>
                    <div class="bordered">
                        <p><strong>maps.stamen.com</strong> remains free (and ad-free), serves upwards of 600 million tiles a month, and takes us hundreds of hours, and thousands of dollars, to sustain.</p>
                        <p>If you find any value or joy in these maps, or if you rely on them for your work or play, please consider a donation in any amount.</p>
                        <!-- <button class="btn">Give Now</button> -->


                        <form method="post" id="payment-form" action="<?php echo(dirname($_SERVER['PHP_SELF']))?>/checkout.php">
                            <section>
                                <div class="bt-drop-in-wrapper">
                                    <div id="bt-dropin"></div>
                                </div>

                                <label for="amount" class="donate-label">
                                    <span class="input-label">Amount</span>
                                    <div class="input-wrapper amount-wrapper">
                                        <input id="amount" name="amount" type="tel" min="1" placeholder="Amount" value="10">
                                    </div>
                                </label>
                            </section>

                            <button class="btn" type="submit"><span>Give Now</span></button>
                        </form>

                    </div>

                </div>
                <div class="span9">
                    <div id="tiles-toner" class="tiles row">
                        <a href="toner/#14/37.8024/-122.2645" class="map span3"
                            data-provider="toner"
                            data-center="37.8024,-122.2645"
                            data-zoom="14"></a>
                        <div class="span6">
                            <h3><a class="hashish" href="toner/">Toner</a></h3>

                            <p>These high-contrast B+W (black and white) maps are
                            featured in our Dotspotting project. They are perfect for data
                            mashups and exploring river meanders and coastal zones.
                            Available in six flavors:
                            <a class="hashish" href="toner/">standard toner</a>,
                            <a class="hashish" href="toner-hybrid/">hybrid</a>,
                            <a class="hashish" href="toner-labels/">labels</a>,
                            <a class="hashish" href="toner-lines/">lines</a>,
                            <a class="hashish" href="toner-background/">background</a>,
                            and <a class="hashish" href="toner-lite/">lite</a>.</p>

                            <p><strong>Available worldwide.</strong></p>

                            <ul>
                                <li><a href="https://github.com/stamen/toner-carto">Fork on GitHub</a></li>
                                <li><a href="http://content.stamen.com/dotspotting_toner_cartography_available_for_download">Read about it</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="tiles-terrain" class="tiles row">
                        <a href="terrain/#11/36.3716/-121.7322" class="map span3"
                            data-provider="terrain"
                            data-center="36.3716,-121.7322"
                            data-zoom="11"></a>
                        <div class="span6">
                            <h3><a class="hashish" href="terrain/">Terrain</a></h3>

                            <p>Orient yourself with our terrain maps, featuring hill shading
                            and natural vegetation colors. These maps showcase advanced
                            labeling and linework generalization of dual-carriageway roads.
                            Terrain was developed in collaboration with Gem Spear and Nelson Minar.
                            Available in four flavors:
                            <a class="hashish" href="terrain/">standard terrain</a>,
                            <a class="hashish" href="terrain-labels/">labels</a>,
                            <a class="hashish" href="terrain-lines/">lines</a>,
                            and <a class="hashish" href="terrain-background/">background</a>.</p>

                            <p><strong>Available in the USA only.</strong></p>

                            <ul>
                                <li><a href="https://github.com/Citytracking/Terrain">Fork on Github</a></li>
                                <li><a href="http://mike.teczno.com/notes/osm-us-terrain-layer/foreground.html">Read about it</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="tiles-watercolor" class="tiles row">
                        <a href="watercolor/#10/37.7682/-122.4451" class="map span3"
                            data-provider="watercolor"
                            data-center="37.7682,-122.4451"
                            data-zoom="9"></a>
                        <div class="span6">
                            <h3><a class="hashish" href="watercolor/">Watercolor</a></h3>
                            <p>Reminiscent of hand drawn maps, our watercolor maps apply
                            raster effect area washes and organic edges over a paper texture
                            to add warm pop to any map. Watercolor was inspired by the
                            <a href="http://www.kickstarter.com/projects/bicycleportraits/bicycle-portraits-a-photographic-book-part-iii-fin">Bicycle Portraits project</a>.
                            Thanks to <a href="http://otherthings.com/blog/">Cassidy Curtis</a> for his early advice.</p>

                            <p><strong>Available worldwide.</strong></p>

                            <ul>
                                <li><a href="http://citytracking.org/talking-maps/">Read about it</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="burningmap" class="tiles row">
                        <a href="burningmap/#10/37.7682/-122.4451" class="span3"><img
                            src="images/burningmap.gif" alt="Really hot heatmaps"></a>
                        <div class="span6">
                            <h3><a class="hashish" href="burningmap/">Burning Map</a></h3>
                            <p>The roof, the roof, the roof is on fire! These "heat
                            maps" use <a href="toner-lines/">toner-lines</a> as the
                            foundation on which to draw fiery animations. It's our way
                            of showing that maps don't have to lie still on the screen
                            anymore, and that we can use the whole world as a canvas
                            for interaction and movement.</p>

                            <p><strong>Requires a WebGL-enabled browser, such as <a href="http://google.com/chrome">Google Chrome</a>.</strong></p>

                            <ul>
                                <li><a href="http://content.stamen.com/announcing_burningmap">Read about it</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="mars" class="tiles row">
                        <a href="mars/" class="span3"><img
                            src="images/mars-thumbnail.png" alt="Mars!"></a>
                        <div class="span6">
                            <h3><a href="mars/">Mars</a>??</h3>
                            <p>Yes, Mars. The Mars Orbiter Laser Altimeter, or MOLA, is an
        					instrument on the Mars Global Surveyor (MGS), a spacecraft that
        					was launched on November 7, 1996.  The MOLA dataset also
        					contains height data, which we've made into a 3D contour map.</p>

                            <p><strong>Requires a WebGL-enabled browser, such as <a href="http://google.com/chrome">Google Chrome</a>.</strong></p>
                            <ul>
                                <!-- <li><a href="#">Read about it</a></li> -->
                                <li><a href="http://mola.gsfc.nasa.gov/">MOLA at NASA</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="trees-cabs-crime" class="tiles row">
                        <a href="trees-cabs-crime/" class="map span3"
                            data-provider="trees-cabs-crime"
                            data-center="37.770,-122.425"
                            data-zoom="13"></a>
                        <div class="span6">
                            <h3><a href="trees-cabs-crime/">Trees, Cabs &amp; Crime</a></h3>
                            <p>Trees, Cabs &amp; Crime started off as a weekend hack
                            and ended up in the Venice Biennale. This map
                            combines three data sets (street tree locations, taxi cab
                            GPS positions, and crime reports) with
                            subtractive blending to reveal halftones hidden in the
                            urban fabric of San Francisco.</p>

                            <p><strong>Available in San Francisco, California.</strong></p>

                            <ul>
                                <li><a href="http://content.stamen.com/trees-cabs-crime_in_venice">Read about it</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="howto">
                <h2>How to Use These Tiles Elsewhere</h2>

                <div id="license" class="row">
                    <h4 class="span2"><a
                            href="http://creativecommons.org/licenses/by/3.0"><img
                        src="https://licensebuttons.net/l/by/3.0/88x31.png"
                        alt="CC-BY License"></a></h4>
                    <div class="span10">
                        <p><em>Except otherwise noted, each of these map tile sets are &copy; Stamen Design, under a <a href="http://creativecommons.org/licenses/by/3.0">Creative Commons Attribution (CC BY 3.0)</a> license.</em></p>
                        <p>We&rsquo;d love to see these maps used around the web, so we&rsquo;ve included some brief instructions to help you use them in the mapping system of your choice.
                        These maps are available free of charge. If you use the tiles we host here, please use this attribution:</p>
                    </div>
                </div>

                <div id="attribution" class="row">
                    <h4 class="span2">Attribution:</h4>
                    <div class="span10">
                        <p>
                        For Toner:
                            <span id="toner-attr">Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.</span>
                            <small><a href="#" id="toner-attr-show-html" >&lt;<span>COPY HTML</span>&gt;</a></small>
                        </p>
                        <textarea rows="3" class="row span10" id="toner-attr-html"></textarea>
                        <script type="text/javascript">
                        (function() {
                            var link = document.getElementById("toner-attr-show-html"),
                                source = document.getElementById("toner-attr"),
                                target = document.getElementById("toner-attr-html"),
                                showing = false;


                            link.addEventListener("click", function(e){
                                e.preventDefault();

                                showing = !showing;
                                if (showing) {
                                    target.value = source.innerHTML;
                                    target.style.display = "block";
                                    target.focus();
                                    target.select();
                                } else {
                                    target.style.display = "none";
                                }

                                return false;
                            });
                        })();
                        </script>
                        <p>
                        For everything else:
                            <span id="attr">Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://creativecommons.org/licenses/by-sa/3.0">CC BY SA</a>.</span>
                            <small><a href="#" id="attr-show-html" >&lt;<span>COPY HTML</span>&gt;</a></small>
                        </p>
                        <textarea rows="3" class="row span10" id="attr-html"></textarea>
                        <script type="text/javascript">
                        (function() {
                            var link = document.getElementById("attr-show-html"),
                                source = document.getElementById("attr"),
                                target = document.getElementById("attr-html"),
                                showing = false;

                            link.addEventListener("click", function(e){
                                e.preventDefault();

                                showing = !showing;
                                if (showing) {
                                    target.value = source.innerHTML;
                                    target.style.display = "block";
                                    target.focus();
                                    target.select();
                                } else {
                                    target.style.display = "none";
                                }

                                return false;
                            });

                        })();
                        </script>
                        <p>If you roll your own tiles from another source you will still need to credit &ldquo;Map data by OpenStreetMap, under ODbL.&rdquo;
                        And if you <em>do</em> use these maps elsewhere, please post a tweet to <a href="http://twitter.com/stamen">@stamen</a>!</p>
                        <p><a href="http://www.openstreetmap.org/copyright">Isn't OSM data provided under the ODbL now?</a> Yes, but the data used in our some of our map tiles pre-dates the license change, so it remains CC BY SA until it's refreshed.</p>
                    </div>
                </div>

                <div class="usage" id="usage-js">
                    <h3>JavaScript Libraries</h3>
                    <div id="usage-intro" class="row">
                        <div class="span12">
                            <p>To use these tiles, just include
                            <a href="js/tile.stamen.js?v1.3.0">our JavaScript</a>
                            alongside your favorite mapping library:</p>
                            <pre class="prettyprint">&lt;script type="text/javascript" src="http://maps.stamen.com/js/tile.stamen.js?v1.3.0"&gt;&lt;/script&gt;</pre>
                            <p>Then, follow the instructions below for your preferred library:</p>
                        </div>
                    </div>

                    <div id="usage-modestmaps">
                        <h4><a href="http://github.com/modestmaps/modestmaps-js">ModestMaps</a></h4>
                        <div class="row">
                            <p class="span4">ModestMaps is a no-frills mapping library by Stamen and friends.
                                View the <a href="test/modestmaps.html">example</a>.</p>
                            <div class="span8">
                                <pre class="prettyprint">// replace "toner" here with "terrain" or "watercolor"
var layer = new MM.StamenTileLayer("toner");
var map = new MM.Map("element_id", layer);
map.setCenterZoom(new MM.Location(37.7, -122.4), 12);</pre>
                            </div>
                        </div>
                    </div>

                    <div id="usage-leaflet">
                        <h4><a href="http://leaflet.cloudmade.com/">Leaflet</a></h4>
                        <div class="row">
                            <p class="span4">Leaflet is a lightweight and easy-to-use library by <a href="http://cloudmade.com">Cloudmade</a>.
                                View the <a href="test/leaflet.html">example</a>.</p>
                            <div class="span8">
                                <pre class="prettyprint">// replace "toner" here with "terrain" or "watercolor"
var layer = new L.StamenTileLayer("toner");
var map = new L.Map("element_id", {
    center: new L.LatLng(37.7, -122.4),
    zoom: 12
});
map.addLayer(layer);</pre>
                            </div>
                        </div>
                    </div>

                    <div id="usage-openlayers">
                        <h4><a href="http://openlayers.org/">OpenLayers</a></h4>
                        <div class="row">
                            <p class="span4">OpenLayers is a hefty and featureful mapping library for use with a variety of GIS applications.
                                View the <a href="test/openlayers.html">example</a>.</p>
                            <div class="span8">
                                <pre class="prettyprint">// replace "toner" here with "terrain" or "watercolor"
var layer = new OpenLayers.Layer.Stamen("toner");
var map = new OpenLayers.Map("element_id");
map.addLayer(layer);</pre>
                            </div>
                        </div>
                    </div>

                    <div id="usage-google">
                        <h4><a href="http://code.google.com/apis/maps/documentation/javascript/">Google Maps</a></h4>
                        <div class="row">
                            <p class="span4">The Google Maps API is ubiquitous and feature-rich, but requires an <a href="#">API key</a> and may <a href="http://code.google.com/apis/maps/faq.html#usage_pricing">cost money</a> if your usage exceeds 25,000 map views per day.
                                View the <a href="test/google.html">example</a>.</p>
                            <div class="span8">
                                <pre class="prettyprint">// replace "toner" here with "terrain" or "watercolor"
var layer = "toner";
var map = new google.maps.Map(document.getElementById("element_id"), {
    center: new google.maps.LatLng(37.7, -122.4),
    zoom: 12,
    mapTypeId: layer,
    mapTypeControlOptions: {
        mapTypeIds: [layer]
    }
});
map.mapTypes.set(layer, new google.maps.StamenMapType(layer));</pre>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="usage" id="usage-elsehwere">
                    <h3>Elsewhere</h3>
                    <p>Many applications and libraries understand the notion of map URL templates.
                    These are ours:</p>
                    <ul>
                        <li><tt>http://tile.stamen.com/toner/{z}/{x}/{y}.png</tt></li>
                        <li><tt>http://tile.stamen.com/terrain/{z}/{x}/{y}.jpg</tt></li>
                        <li><tt>http://tile.stamen.com/watercolor/{z}/{x}/{y}.jpg</tt></li>
                    </ul>
                </div>

                <div class="usage" id="usage-ssl">
                    <h3>SSL</h3>
                    <p>If you'd like to display these map tiles on a website
                    that requires HTTPS, use our tile SSL endpoint by replacing
                    <code>http://tile.stamen.com</code> with
                    <code>https://stamen-tiles.a.ssl.fastly.net</code>.
                    Multiple subdomains can be also be used:
                    <code>https://stamen-tiles-{S}.a.ssl.fastly.net</code></p>
                    <p>JavaScript can be loaded from
                    <code>https://stamen-maps.a.ssl.fastly.net/js/tile.stamen.js</code>.</p>
                    <p>If you need protocol-agnostic URLs, use <code>//stamen-tiles-{s}.a.ssl.fastly.net/</code>, as that endpoint will work for both SSL and non-SSL connections.</p>
                </div>

            </div>

            <div id="footer" class="row light">
                <p id="credit" class="span9 light">These tiles are made available as part of the
                <a href="http://citytracking.org">CityTracking</a> project,
                funded by the <a href="http://www.knightfoundation.org/">Knight Foundation</a>,
                in which Stamen is building web services and
                <a href="http://github.com/Citytracking">open source tools</a>
                to display public data in easy-to-understand, highly visual ways.</p>
                <div id="logo" class="span3">
                    <a href="http://citytracking.org"><img src="images/citytracking-logo.png" alt="CityTracking"></a>
                </div>
            </div>

        </div>

        <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
        <script src="//cdn.maptiks.com/maptiks-leaflet.min.js"></script>
        <script type="text/javascript" src="js/polyfills.js"></script>
        <script type="text/javascript" src="js/vendor/reqwest.min.js"></script>
        <script type="text/javascript" src="js/tile.stamen.js?v1.3.0"></script>
        <script type="text/javascript" src="js/common.js?recaptcha"></script>
        <script type="text/javascript" src="js/vendor/google-code-prettify/prettify.js"></script>
        <script src="https://js.braintreegateway.com/js/braintree-2.26.0.min.js"></script>

        <script type="text/javascript" src="js/donate.js"></script>
        <script type="text/javascript" src="js/index.js?20130425" defer></script>
        <!-- google code prettify -->
        <script type="text/javascript" defer>
            prettyPrint();

            var checkout = new Donate({
                formID: 'payment-form'
            });

            // donate form
            var client_token = "<?php echo(Braintree\ClientToken::generate()); ?>";
            braintree.setup(client_token, "dropin", {
                container: "bt-dropin"
            });

            // close btns
            var closeButtons = document.querySelectorAll('button.close');
            for (var i = 0; i<closeButtons.length; i++) {
                closeButtons[i].addEventListener('click', function(e) {
                    var alert = this.closest('.notice-wrapper');
                    alert.parentNode.removeChild(alert);
                });
            }
        </script>
    </body>
</html>

