Drop your MP4 here as 'footer-bg.mp4'.

The Kresna footer references this file via:
  <?php echo get_template_directory_uri(); ?>/assets/video/footer-bg.mp4

Fallback: solid #1e4fc0 background until the file exists.

Recommended specs:
- Format: MP4 (H.264) + WebM (VP9) for fallback
- Resolution: 720x720 (1:1) or 800x500 (8:5)
- Duration: 8-15 seconds (loop seamlessly)
- Bitrate: 1-2 Mbps (target <2 MB total)
- Audio: stripped (the player is muted anyway)
