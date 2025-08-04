<?php

/**
 * TOC Helper Functions for AI Post Generation
 * This file contains functions to enhance Table of Contents functionality
 * for both single and bulk AI post generation
 */

if (!function_exists('verifyAndFixTOCLinks')) {
    /**
     * Function to verify and fix Table of Contents links
     * This function ensures that TOC links properly connect to their corresponding headings
     */
    function verifyAndFixTOCLinks($content) {
        // Parse the content to extract TOC links and headings
        $toc_links = [];
        $headings = [];
        
        // Extract TOC links using regex
        preg_match_all('/<a href="#([^"]+)">([^<]+)<\/a>/', $content, $toc_matches);
        if (!empty($toc_matches[1])) {
            foreach ($toc_matches[1] as $index => $anchor) {
                $toc_links[$anchor] = $toc_matches[2][$index];
            }
        }
        
        // Extract headings with IDs using regex
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $heading_matches);
        if (!empty($heading_matches[2])) {
            foreach ($heading_matches[2] as $index => $id) {
                if (!empty($id)) {
                    $headings[$id] = $heading_matches[3][$index];
                }
            }
        }
        
        // Check for missing IDs and fix them
        $updated_content = $content;
        
        foreach ($toc_links as $anchor => $title) {
            if (!isset($headings[$anchor])) {
                // Find the heading with matching title and add ID
                $heading_pattern = '/<h([2-6])(?:\s+[^>]*)?>(' . preg_quote(trim($title), '/') . ')<\/h[2-6]>/i';
                $replacement = '<h$1 id="' . $anchor . '">$2</h$1>';
                $updated_content = preg_replace($heading_pattern, $replacement, $updated_content, 1);
            }
        }
        
        // Generate missing TOC links for headings without them
        foreach ($headings as $id => $title) {
            if (!isset($toc_links[$id])) {
                // Log missing TOC link (for debugging)
                error_log("Missing TOC link for heading: " . $title . " (ID: " . $id . ")");
            }
        }
        
        return $updated_content;
    }
}

if (!function_exists('generateAnchorId')) {
    /**
     * Function to generate URL-friendly anchor IDs from heading text
     */
    function generateAnchorId($text) {
        // Remove HTML tags, convert to lowercase, replace spaces and special chars with hyphens
        $id = strtolower(strip_tags($text));
        $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
        $id = preg_replace('/[\s-]+/', '-', $id);
        $id = trim($id, '-');
        return $id;
    }
}

if (!function_exists('generateTOCFromContent')) {
    /**
     * Function to automatically generate TOC from content headings
     */
    function generateTOCFromContent($content) {
        preg_match_all('/<h([2-6])(?:\s+id="([^"]+)")?>([^<]+)<\/h[2-6]>/', $content, $matches);
        
        if (empty($matches[0])) {
            return '<h2 id="table-of-contents">Table of Contents</h2><p>No headings found.</p>';
        }
        
        $toc = '<h2 id="table-of-contents">Table of Contents</h2><ul>';
        
        foreach ($matches[0] as $index => $match) {
            $level = $matches[1][$index];
            $id = !empty($matches[2][$index]) ? $matches[2][$index] : generateAnchorId($matches[3][$index]);
            $title = trim($matches[3][$index]);
            
            // Only include H2 and H3 in TOC for better readability
            if ($level <= 3) {
                $indent = $level == 3 ? 'style="margin-left: 20px;"' : '';
                $toc .= '<li ' . $indent . '><a href="#' . $id . '">' . $title . '</a></li>';
            }
        }
        
        $toc .= '</ul>';
        return $toc;
    }
}

if (!function_exists('enhanceContentWithTOC')) {
    /**
     * Function to enhance content with proper TOC formatting
     * This is a comprehensive function that applies all TOC enhancements
     */
    function enhanceContentWithTOC($content) {
        // First verify and fix existing TOC links
        $enhanced_content = verifyAndFixTOCLinks($content);
        
        // Additional enhancements can be added here in the future
        // - Smooth scrolling JavaScript
        // - Back to top links
        // - Section numbering
        
        return $enhanced_content;
    }
}

?>
