<?php
    /*
     * $Id: chart_rules.php 1439 2009-11-17 23:31:04Z dmorton $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2005 by Robert LeBlanc <rjl@renaissoft.com>
     *               and David Morton <mortonda@dgrmm.net>
     * All rights reserved.
     *
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com> and David Morton<mortonda@dgrmm.net>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */


    require_once ("core.php");
    require_once ("authcheck.php");
    require_once ("constants.php");
    require_once ("maia_db.php");
    $display_language = get_display_language($euid);
    require_once ("./locale/$display_language/rulestats.php");

    require 'Image/Graph.php';
    
    $chart_colors = array ( 'green@0.2', 'blue@0.2', 'red@0.2', 'yellow@0.2', 'orange@0.2', 'purple@0.2', 
                            'green@0.8', 'blue@0.8', 'red@0.8', 'yellow@0.8', 'orange@0.8', 'purple@0.8',
                            'green@0.4', 'blue@0.4', 'red@0.4', 'yellow@0.4', 'orange@0.4', 'purple@0.4',
                            'green@0.6', 'blue@0.6', 'red@0.6', 'yellow@0.6', 'orange@0.6', 'purple@0.6',
                            'green', 'blue', 'red', 'yellow', 'orange', 'purple');
     
    if(!empty($_GET['thumb'])) {
        $out = array(
            'limit' => 7,
            'width' => 400,
            'height' => 200,
            'center' => 100,
            'margin_top' => 50,
            'margin_bottom' => 20,
            'margin_left' => 160,
            'margin_right' => 30
        );
    } else {
        $out = array(
            'limit' => 25,
            'width' => 700,
            'height' => 500,
            'center' => 250,
            'margin_top' => 60,
            'margin_bottom' => 30,
            'margin_left' => 180,
            'margin_right' => 30
        );
    }
    
    // create the graph
    $Graph =& Image_Graph::factory('graph', array(800, 600));
    // add a TrueType font
    $Font =& $Graph->addNew('ttf_font', $chart_font);
    // set the font size to 11 pixels
    $Font->setSize(8);
    
    $Graph->setFont($Font);
    // create the plotareas
    
    
    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::factory('title', array('SpamAssassin rules', 12)),
            Image_Graph::horizontal(
                $Plotarea = Image_Graph::factory('plotarea'),
                $Legend = Image_Graph::factory('legend'),
                60
            ),
            5
        )
    );
    
    $Legend->setPlotarea($Plotarea);
    $Legend->setAlignment(IMAGE_GRAPH_ALIGN_VERTICAL);
    
    $Plotarea->hideAxis();

    // create the dataset
    $Dataset =& Image_Graph::factory('dataset');

    
    $select = "SELECT rule_name, rule_count " .
              "FROM maia_sa_rules WHERE rule_count > 0 ORDER BY rule_count DESC, rule_name ASC LIMIT ?";
    $sth = $dbh->prepare($select);
    $res = $sth->execute($out['limit']);
    // if (PEAR::isError($sth)) {
    if ((new PEAR)->isError($sth)) {
        die($sth->getMessage());
    }

    $keys = array();
    $values = array();
    if($res->numRows()) {
        $sum = 0;
        while ($row = $res->fetchRow()) {
            $Dataset->addPoint($row["rule_name"], $row["rule_count"], $row["rule_name"]);
            $keys[] = $row["rule_name"];
            $values[] = $row["rule_count"];
            $sum += $row["rule_count"];
        }
        $select = "SELECT (SUM(rule_count)-?) AS rest FROM maia_sa_rules";

        $sth = $dbh->prepare($select);
        $res = $sth->execute($sum);
        // if (PEAR::isError($sth)) {
        if ((new PEAR)->isError($sth)) {
            die($res->getMessage());
        }
        if($res->numRows()) {
            $row = $res->fetchrow();
            if(0 && $row["rest"]) {
                $Dataset->addPoint("Rest", $row["rest"], "Rest");
                $keys[] = "Rest";
                $values[] = $row["rest"];
            }
        }
    }

    // Create the Pie Graph.
// create the 1st plot as smoothed area chart using the 1st dataset
    $Plot =& $Plotarea->addNew('Image_Graph_Plot_Pie', $Dataset);

    $Plot->Radius = 2;
        
    // set a line color
    $Plot->setLineColor('gray');
    
    // set a standard fill style
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    $Plot->setFillStyle($FillArray);
    for ($i = 0 ; $i < count($keys); $i++) {
      $FillArray->addColor($chart_colors[$i]);
    }
    


    
    $Plot->explode(10);

    // output the Graph
    $Graph->done();
 
?>

