/**
 * app-academy-course Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Media player

  const videoPlayer = new Plyr('#guitar-video-player');

  const videoPlayer2 = new Plyr('#guitar-video-player-2');

  document.getElementsByClassName('plyr')[0].style.borderRadius = '6px';
  document.getElementsByClassName('plyr')[1].style.borderRadius = '6px';
  document.getElementsByClassName('plyr__poster')[0].style.display = 'none';
  document.getElementsByClassName('plyr__poster')[1].style.display = 'none';
});
