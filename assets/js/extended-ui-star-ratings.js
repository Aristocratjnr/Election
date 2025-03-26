/**
 * Star Ratings (js)
 */

'use strict';
document.addEventListener('DOMContentLoaded', function (e) {
  const basicRatings = document.querySelector('.basic-ratings'),
    readOnlyRatings = document.querySelector('.read-only-ratings'),
    customSvg = document.querySelector('.custom-svg-ratings'),
    halfStar = document.querySelector('.half-star-ratings'),
    onSetEvents = document.querySelector('.onset-event-ratings'),
    onChangeEvents = document.querySelector('.onChange-event-ratings'),
    ratingMethods = document.querySelector('.methods-ratings'),
    initializeRatings = document.querySelector('.btn-initialize'),
    destroyRatings = document.querySelector('.btn-destroy'),
    getRatings = document.querySelector('.btn-get-rating'),
    setRatings = document.querySelector('.btn-set-rating'),
    iconStar = document.querySelector('.icon-star-ratings'),
    iconStarSm = document.querySelector('.icon-star-sm-ratings'),
    iconStarMd = document.querySelector('.icon-star-md-ratings'),
    iconStarLg = document.querySelector('.icon-star-lg-ratings'),
    iconStarPrimary = document.querySelector('.icon-star-primary-ratings'),
    iconStarWarning = document.querySelector('.icon-star-warning-ratings'),
    iconStarSuccess = document.querySelector('.icon-star-success-ratings'),
    iconStarDanger = document.querySelector('.icon-star-danger-ratings');

  let r = parseInt(window.Helpers.getCssVar('gray-200', true).slice(1, 3), 16);
  let g = parseInt(window.Helpers.getCssVar('gray-200', true).slice(3, 5), 16);
  let b = parseInt(window.Helpers.getCssVar('gray-200', true).slice(5, 7), 16);

  const halfStarGray = window.Helpers.getCssVar('gray-200', true).replace('#', '%23');
  const halfStarGradient = isRtl
    ? "%3Cstop offset='50%25' style='stop-color:" +
      halfStarGray +
      "' /%3E%3Cstop offset='50%25' style='stop-color:%23FFD700' /%3E"
    : "%3Cstop offset='50%25' style='stop-color:%23FFD700' /%3E%3Cstop offset='50%25' style='stop-color:" +
      halfStarGray +
      "' /%3E";
  const fullStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='%23FFD700' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E";

  const halfStarSVG = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cdefs%3E%3ClinearGradient id='halfStarGradient'%3E${halfStarGradient}%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23halfStarGradient)' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E`;

  const emptyStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='rgb(" +
    r +
    ',' +
    g +
    ',' +
    b +
    ")' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E";

  // Basic Ratings
  // --------------------------------------------------------------------

  if (basicRatings) {
    let ratings = new Raty(basicRatings, {
      starOn: fullStarSVG,
      starHalf: halfStarSVG,
      starOff: emptyStarSVG
    });
    ratings.init();
  }

  // Read Only Ratings
  // --------------------------------------------------------------------
  if (readOnlyRatings) {
    let ratings = new Raty(readOnlyRatings, {
      number: 5,
      starOn: fullStarSVG,
      starHalf: halfStarSVG,
      starOff: emptyStarSVG
    });
    ratings.init();
  }

  // custom-svg icons
  const customFullStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23FFD700' d='m6.516 14.323l-1.49 6.452a.998.998 0 0 0 1.529 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082a1 1 0 0 0-.59-1.74l-5.701-.454l-2.467-5.461a.998.998 0 0 0-1.822 0L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713zm2.853-4.326a1 1 0 0 0 .832-.586L12 5.43l1.799 3.981a1 1 0 0 0 .832.586l3.972.315l-3.271 2.944c-.284.256-.397.65-.293 1.018l1.253 4.385l-3.736-2.491a.995.995 0 0 0-1.109 0l-3.904 2.603l1.05-4.546a1 1 0 0 0-.276-.94l-3.038-2.962z'/%3E%3C/svg%3E";

  const customEmptyStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='rgb(" +
    r +
    ',' +
    g +
    ',' +
    b +
    ")' d='m6.516 14.323l-1.49 6.452a.998.998 0 0 0 1.529 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082a1 1 0 0 0-.59-1.74l-5.701-.454l-2.467-5.461a.998.998 0 0 0-1.822 0L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713zm2.853-4.326a1 1 0 0 0 .832-.586L12 5.43l1.799 3.981a1 1 0 0 0 .832.586l3.972.315l-3.271 2.944c-.284.256-.397.65-.293 1.018l1.253 4.385l-3.736-2.491a.995.995 0 0 0-1.109 0l-3.904 2.603l1.05-4.546a1 1 0 0 0-.276-.94l-3.038-2.962z'/%3E%3C/svg%3E";

  // Custom SVG Ratings
  // --------------------------------------------------------------------
  if (customSvg) {
    let ratings = new Raty(customSvg, {
      starOn: customFullStarSVG,
      starOff: customEmptyStarSVG
    });
    ratings.init();
  }

  // Half Star Ratings
  // --------------------------------------------------------------------
  if (halfStar) {
    let ratings = new Raty(halfStar, {
      starOn: fullStarSVG,
      starHalf: halfStarSVG,
      starOff: emptyStarSVG,
      rtl: isRtl
    });
    ratings.init();
  }

  // Ratings Events
  // --------------------------------------------------------------------

  // onSet Event
  if (onSetEvents) {
    let ratings = new Raty(onSetEvents, {
      starOn: fullStarSVG,
      starHalf: halfStarSVG,
      starOff: emptyStarSVG,
      click: function (score) {
        alert('The rating is set to ' + score + ' !');
      }
    });
    ratings.init();
  }

  // onChange Event
  if (onChangeEvents) {
    let ratings = new Raty(onChangeEvents, {
      starOn: fullStarSVG,
      starHalf: halfStarSVG,
      starOff: emptyStarSVG,
      half: true,
      mouseover: function (score) {
        const counterElement = onChangeEvents.parentElement.querySelector('.counter');
        if (counterElement) {
          counterElement.textContent = (Math.round(score * 2) / 2).toFixed(1);
        }
      },
      mouseout: function () {
        const counterElement = onChangeEvents.parentElement.querySelector('.counter');
        if (counterElement) {
          const currentScore = ratings.score() || 0;
          counterElement.textContent = (Math.round(currentScore * 2) / 2).toFixed(1);
        }
      }
    });

    const initialCounter = onChangeEvents.parentElement.querySelector('.counter');
    if (initialCounter) {
      initialCounter.textContent = '0.0';
    }

    ratings.init();
  }

  // Ratings Methods
  // --------------------------------------------------------------------
  let currentScore = 0;
  let ratingInstance = null;

  // Initialize rating
  function initializeRating(score = currentScore) {
    if (!ratingInstance) {
      ratingInstance = new Raty(ratingMethods, {
        starOn: fullStarSVG,
        starHalf: halfStarSVG,
        starOff: emptyStarSVG,
        click: function (newScore) {
          currentScore = newScore;
        },
        score: score,
        readOnly: false,
        rtl: isRtl
      });
      ratingInstance.init();
    }
  }

  initializeRating(currentScore);
  // Destroy rating
  function destroyRating() {
    if (ratingInstance) {
      ratingMethods.innerHTML = '';
      ratingInstance = null;
      currentScore = 0;
    } else {
      alert('Please Initialize Ratings First');
    }
  }

  // Get Current Rating
  function getRating() {
    if (ratingInstance) {
      alert('Current Ratings are ' + currentScore);
    } else {
      alert('Please Initialize Ratings First');
    }
  }

  function setRating(score) {
    if (ratingInstance) {
      destroyRating();
      initializeRating(score);
      currentScore = score;
    } else {
      alert('Please Initialize Ratings First');
    }
  }

  if (initializeRatings) {
    initializeRatings.addEventListener('click', () => initializeRating(currentScore));
  }

  if (destroyRatings) {
    destroyRatings.addEventListener('click', destroyRating);
  }

  if (getRatings) {
    getRatings.addEventListener('click', getRating);
  }

  if (setRatings) {
    setRatings.addEventListener('click', () => setRating(1));
  }

  // icon Star Ratings
  // --------------------------------------------------------------------
  if (iconStar) {
    let ratings = new Raty(iconStar, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-heart bg-danger-subtle',
      starOn: 'icon-base icon-xl bx bxs-heart text-danger'
    });
    ratings.init();
  }

  // size variant star Ratings
  // --------------------------------------------------------------------

  if (iconStarSm) {
    let ratings = new Raty(iconStarSm, {
      starType: 'i',
      starOff: 'icon-base bx bxs-star bg-secondary-subtle',
      starOn: 'icon-base bx bxs-star text-secondary'
    });
    ratings.init();
  }

  if (iconStarMd) {
    let ratings = new Raty(iconStarMd, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-star bg-secondary-subtle',
      starOn: 'icon-base icon-xl bx bxs-star text-secondary'
    });
    ratings.init();
  }

  if (iconStarLg) {
    let ratings = new Raty(iconStarLg, {
      starType: 'i',
      starOff: 'icon-base icon-42px bx bxs-star bg-secondary-subtle',
      starOn: 'icon-base icon-42px bx bxs-star text-secondary'
    });
    ratings.init();
  }

  // color variant star Ratings
  // --------------------------------------------------------------------

  if (iconStarPrimary) {
    let ratings = new Raty(iconStarPrimary, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-star bg-primary-subtle',
      starOn: 'icon-base icon-xl bx bxs-star text-primary'
    });
    ratings.init();
  }

  if (iconStarWarning) {
    let ratings = new Raty(iconStarWarning, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-star bg-warning-subtle',
      starOn: 'icon-base icon-xl bx bxs-star text-warning'
    });
    ratings.init();
  }

  if (iconStarSuccess) {
    let ratings = new Raty(iconStarSuccess, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-star bg-success-subtle',
      starOn: 'icon-base icon-xl bx bxs-star text-success'
    });
    ratings.init();
  }

  if (iconStarDanger) {
    let ratings = new Raty(iconStarDanger, {
      starType: 'i',
      starOff: 'icon-base icon-xl bx bxs-star bg-danger-subtle',
      starOn: 'icon-base icon-xl bx bxs-star text-danger'
    });
    ratings.init();
  }
});
