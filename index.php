<?php
// Set headers to prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Speed - BenHaynie</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  </head>
  <body>
  <?php
  // Expiremental code for global scores
    function save_score($scoreName, $score) {
      $file = fopen('scores.txt', 'a');
      if ($file) {
        fwrite($file, $scoreName . "," . $score . "\n");
        fclose($file);
      }
    }

    function get_scores() {
      $scores = array();
      $file = fopen('scores.txt', 'r');
      if ($file) {
        while (($line = fgets($file)) !== false) {
          $parts = explode(',', trim($line));
          if(count($parts) == 2) {
            $scores[] = array('scoreName' => $parts[0], 'score' => (int)$parts[1]);
          }
        }
        fclose($file);
      }
      usort($scores, function($a, $b) {
        return $a['score'] - $b['score'];
      });
      return array_slice($scores, 0, 10);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['scoreName']) && isset($_POST['score'])) {
      save_score($_POST['scoreName'], $_POST['score']);
    }

    $scores = get_scores();

    

  ?>

  <!-- Name prompts modal for score -->
  <!-- <div id="nameModal" class="modal">
    <div class="modal-content better-border">
      <span class="close">&times;</span>
      <h2>Please enter your name for score keeping</h2><br>
      <input type="text" id="userName" placeholder="  First name" class="better-border p-2" > <br><br>
      <button id="submitName" class="better-border p-2">Submit</button>
    </div> -->
  </div>

  <div class="main-content" id="mainContent">

    <div class="scoreboard"id="scoreboard">
      <p>SCOREBOARD</p>
      <div id="topTen"></div>
      <div class="shadow-lg score-name p-2">
        <h3>Enter Score Name:</h3>
        <input type="text" id="userName" placeholder=" First Name" class="better-border mt-2" >
        <button id="submitName" class="p-1 mt-2">Submit</button>
      </div>
      <?php
        // foreach ($scores as $name => $score) {
          // echo"$name: $score<br>";
        // }
      ?>
    </div>
    <div class="clock" id="timerDisplay">00:00</div>

    <div class="player2">
      <h1 class="text-center text-6xl text-white pb-0 mb-0">Welcome</h1>
      <p class="text-white text-2xl pl-6 pt-0 mt-0">
        The game is Speed <br>
        The type is Player vs Self <br>
        How to play: <br>
        - The keys A, S, D, F, and space control selection of the five cards in your hand. <br>
        - The keys J, K, L and ; control selection of the four table cards you want to play on. <br>
        - Pressing ' R ' will place new cards on the four table cards if you get stuck. <br>
        - Pressing ' Enter ' will shuffle the deck, deal the cards and start the timer. <br>
        - If you have remainder cards, pressing ' T ' will stop the timer and calculate your score. <br>
        - Normal rules of Speed apply. <br>
      </p>
    </div>
    <div class="neutral-zone grid place-content-center">
      <!-- Neutral cards -->
        <div class="grid grid-cols-6 gap-20">
        <div class="rounded overflow-hidden shadow-lg play-pile grid place-content-center h-64 card-number" id="playPile1"></div>
        <div class="rounded overflow-hidden shadow-lg play-pile grid place-content-center h-64 card-number" id="playPile2"></div>
        <div class="rounded overflow-hidden shadow-lg play-pile grid place-content-center h-64 card-number" id="playPile3"></div>
        <div class="rounded overflow-hidden shadow-lg play-pile grid place-content-center h-64 card-number" id="playPile4"></div>
        <div class="rounded neutral-draw-pile" id="neutralDrawPile"></div>
        <div id="neutral-draw-pile-count"></div>
        </div>
    </div>
    <!-- Player 1 -->
    <div class="grid place-content-center">
      <div class="player1 grid grid-cols-7 gap-10 place-content-center">
          <div class="rounded overflow-hidden shadow-lg hand-pile grid place-content-center h-50 card-number" id="p1HandC1"></div>
          <div class="rounded overflow-hidden shadow-lg hand-pile grid place-content-center h-50 card-number" id="p1HandC2"></div>
          <div class="rounded overflow-hidden shadow-lg hand-pile grid place-content-center h-50 card-number" id="p1HandC3"></div>
          <div class="rounded overflow-hidden shadow-lg hand-pile grid place-content-center h-50 card-number" id="p1HandC4"></div>
          <div class="rounded overflow-hidden shadow-lg hand-pile grid place-content-center h-50 card-number" id="p1HandC5"></div>
          <div class="rounded draw-pile" id="drawPile"></div>
          <div id="draw-pile-count"></div>
            
      </div>
    </div>
  </div>
      







    



<script>
  // const modal = document.getElementById("nameModal");
  const submitNameButton = document.getElementById("submitName");
  const userNameInput = document.getElementById("userName");
  const mainContent = document.getElementById("mainContent");
  let score;
  let scoreName;


  function disableKeydown(event) {
  if (event.target !== userNameInput) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  }

  window.onload = function() {
    // document.addEventListener('keydown', disableKeydown, true);
  }

  submitNameButton.onclick = function() {
    let userName = userNameInput.value.trim().toLowerCase();
    
    if (userName) {
      // modal.style.display = 'none';
      mainContent.style.filter = 'none';
      mainContent.style.pointerEvents = 'auto';
      // document.removeEventListener('keydown', disableKeydown, true);
      scoreName = userName;
    } else {
      x= "You need to enter a name to play";
    }
  }
  
  const scoreboard = document.getElementById("scoreboard");
  const topTen = document.getElementById("topTen");
  
  

  const timerDisplay = document.getElementById("timerDisplay");
  let timer;
  let totalSeconds = 0;

  function startTimer() {
    timer = setInterval(() => {
      totalSeconds++;
      updateTimerDisplay();
    }, 1000);
  }

  function stopTimer() {
    clearInterval(timer);
    timer = null;
  }

  function resetTimer() {
    totalSeconds = 0;
    updateTimerDisplay();
  }

  function updateTimerDisplay() {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const formattedMinutes = String(minutes).padStart(2, '0');
    const formattedSeconds = String(seconds).padStart(2, '0');
    timerDisplay.textContent = `${formattedMinutes}:${formattedSeconds}`;
    score = (minutes * 60) + seconds;
    // console.log("timer second +1 =", score);
    calcScore();
    

  }

  function calcScore() {
    const elements = [p1HandC1, p1HandC2, p1HandC3, p1HandC4, p1HandC5];
    let clearedCount = 0;
    elements.forEach((element, index) => {
      if (element.innerHTML === '' || element.innerHTML === ' ') {
        clearedCount++;
      }
    })


    if (clearedCount === 5) {
      stopTimer();
    }

    if (timer === null) {
      switch(clearedCount) {
      case 0 : 
        score += 150;
        // console.log("cleared count 0 +150 =", score);
        break;
      case 1 :
        score += 120;
        // console.log("cleared count +120 =", score);
        break;
      case 2 : 
        score += 90;
        // console.log("cleared count 2 +90 =", score);
        break;
      case 3 : 
        score += 60;
        // console.log("cleared count 3 +60 =", score);
        break;
      case 4 : 
        score += 30;
        // console.log("cleared count 4 +30 =", score);
        break;
      case 5 : 
        score -= 60;
        // console.log("cleared count 5 -60 =", score);
        stopTimer();
        score += 70;
        if (scoreName !== undefined) {
          submitScore();
        }else {
          scoreName = "noname";
          topTen.innerHTML += `${scoreName}: ${score}`;
        }
        // console.log("case 5 cleared, +70 =", score);
        break;
    }
    }
    
  }

  //end timer and calc score
  document.addEventListener('keydown', function(event) {
    if (event.key === 't' || event.key === 'T') {
      stopTimer();
      score += (parseInt(drawPileCount.innerHTML) * 30);
      // console.log("pressed T drawPileCount * 30. drawPileCount:", drawPileCount.innerHTML, "score:", score);
      calcScore();
      // topTen.innerHTML = `${scoreName}: ${score + 70}`;
      // console.log("pressed T score +70 =", score);
      score += 70;
      if (scoreName !== undefined) {
          submitScore();
        }else {
          scoreName = "noname";
          topTen.innerHTML += `${scoreName}: ${score}`;
        }
    }
  })







  const playPile1 = document.getElementById("playPile1");
  const playPile2 = document.getElementById("playPile2");
  const playPile3 = document.getElementById("playPile3");
  const playPile4 = document.getElementById("playPile4");
  let playPile1Number = Math.floor(Number(playPile1.innerHTML));
  let playPile2Number = Math.floor(Number(playPile2.innerHTML));
  let playPile3Number = Math.floor(Number(playPile3.innerHTML));
  let playPile4Number = Math.floor(Number(playPile4.innerHTML));

  const p1HandC1 = document.getElementById("p1HandC1");
  const p1HandC2 = document.getElementById("p1HandC2");
  const p1HandC3 = document.getElementById("p1HandC3");
  const p1HandC4 = document.getElementById("p1HandC4");
  const p1HandC5 = document.getElementById("p1HandC5");
  let p1HandC1Number = Math.floor(Number(p1HandC1.innerHTML));
  let p1HandC2Number = Math.floor(Number(p1HandC2.innerHTML));
  let p1HandC3Number = Math.floor(Number(p1HandC3.innerHTML));
  let p1HandC4Number = Math.floor(Number(p1HandC4.innerHTML));
  let p1HandC5Number = Math.floor(Number(p1HandC5.innerHTML));

  const neutralDrawPile = document.getElementById("neutralDrawPile");
  const neutralDrawPileCount = document.getElementById("neutral-draw-pile-count");
  const drawPile = document.getElementById("drawPile");
  const drawPileCount = document.getElementById("draw-pile-count");

  let fullDeck = [1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3, 6.4, 7.1, 7.2, 7.3, 7.4, 8.1, 8.2, 8.3, 8.4, 9.1, 9.2, 9.3, 9.4, 10.1, 10.2, 10.3, 10.4, 11.1, 11.2, 11.3, 11.4, 12.1, 12.2, 12.3, 12.4, 13.1, 13.2, 13.3, 13.4];
  function shuffle(array) {
    for (let i = array.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
  }
  //order of type = spade, club, dimond, heart
  function cardImages() {
    const elements = [p1HandC1, p1HandC2, p1HandC3, p1HandC4, p1HandC5, playPile1, playPile2, playPile3, playPile4];
    elements.forEach((element, index) => {
      const number = parseFloat(element.innerHTML, 10);

      element.style.textIndent = '-500px';


      switch(number){
        case 1.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/ace_of_spades2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 1.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/ace_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 1.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/ace_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 1.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/ace_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 2.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/2_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 2.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/2_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 2.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/2_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 2.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/2_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 3.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/3_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 3.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/3_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 3.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/3_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 3.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/3_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 4.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/4_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 4.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/4_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 4.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/4_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 4.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/4_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 5.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/5_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 5.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/5_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 5.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/5_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 5.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/5_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 6.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/6_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 6.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/6_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 6.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/6_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 6.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/6_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 7.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/7_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 7.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/7_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 7.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/7_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 7.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/7_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 8.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/8_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 8.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/8_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 8.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/8_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 8.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/8_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 9.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/9_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 9.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/9_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 9.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/9_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 9.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/9_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 10.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/10_of_spades.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 10.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/10_of_clubs.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 10.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/10_of_diamonds.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 10.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/10_of_hearts.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 11.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/jack_of_spades2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 11.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/jack_of_clubs2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 11.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/jack_of_diamonds2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 11.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/jack_of_hearts2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 12.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/queen_of_spades2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 12.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/queen_of_clubs2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 12.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/queen_of_diamonds2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 12.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/queen_of_hearts2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 13.1: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/king_of_spades2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 13.2: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/king_of_clubs2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 13.3: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/king_of_diamonds2.png)';
          element.style.backgroundSize = 'cover';
          break;
        case 13.4: 
          element.style.backgroundImage = 'url(assets/PNG-cards-1.3/king_of_hearts2.png)';
          element.style.backgroundSize = 'cover';
          break;
          default: 
          element.style.backgroundImage = 'none';

        
        }



      //div.style.backgroundImage = 'img'
    })
  }



  let shuffledDeck = shuffle(fullDeck);
  let p1DrawPileCurrentIndex = 30;
  

//pressing enter 
  function firstDeal() {
    semicolonPressCount = 1;
    shuffle(fullDeck);
    
    playPile1.innerHTML = shuffledDeck[51];
    playPile2.innerHTML = shuffledDeck[50];
    playPile3.innerHTML = shuffledDeck[49];
    playPile4.innerHTML = shuffledDeck[48]
    p1HandC1.innerHTML = shuffledDeck[47];
    p1HandC2.innerHTML = shuffledDeck[46];
    p1HandC3.innerHTML = shuffledDeck[45];
    p1HandC4.innerHTML = shuffledDeck[44];
    p1HandC5.innerHTML = shuffledDeck[43];
    // index 42-31 are for playDrawPile
    // index 30-0 are for p1DrawPile
    playPile1Number = Math.floor(Number(playPile1.innerHTML));
    playPile2Number = Math.floor(Number(playPile2.innerHTML));
    playPile3Number = Math.floor(Number(playPile3.innerHTML));
    playPile4Number = Math.floor(Number(playPile4.innerHTML));
    p1HandC1Number = Math.floor(Number(p1HandC1.innerHTML));
    p1HandC2Number = Math.floor(Number(p1HandC2.innerHTML));
    p1HandC3Number = Math.floor(Number(p1HandC3.innerHTML));
    p1HandC4Number = Math.floor(Number(p1HandC4.innerHTML));
    p1HandC5Number = Math.floor(Number(p1HandC5.innerHTML));

    neutralDrawPileCount.innerHTML = '3  <br>Resets';
    neutralDrawPile.style.backgroundImage = 'url(assets/PNG-cards-1.3/BackOfPlayingCard.jpg)';
    drawPile.style.backgroundImage = 'url(assets/PNG-cards-1.3/BackOfPlayingCard.jpg)';
    drawPileCount.innerHTML = 31;
    p1DrawPileCurrentIndex = 30;

  }


  function dealNextCard() {
    let handDivs = ['p1HandC1', 'p1HandC2', 'p1HandC3', 'p1HandC4', 'p1HandC5'];

    for (let i = 0; i< handDivs.length; i++) {
      let div = document.getElementById(handDivs[i]);
      if(div.innerHTML === '' && p1DrawPileCurrentIndex >= 0) {
        div.innerHTML = shuffledDeck[p1DrawPileCurrentIndex];
        //needs to change handCNumber = shuffledDeck[p1DrawPileCurrentIndex];
        switch (handDivs[i]) {
          case 'p1HandC1':
            p1HandC1Number = Math.floor(Number(shuffledDeck[p1DrawPileCurrentIndex]));
            break;
          case 'p1HandC2':
            p1HandC2Number = Math.floor(Number(shuffledDeck[p1DrawPileCurrentIndex]));
            break;
          case 'p1HandC3':
            p1HandC3Number = Math.floor(Number(shuffledDeck[p1DrawPileCurrentIndex]));
            break;
          case 'p1HandC4':
            p1HandC4Number = Math.floor(Number(shuffledDeck[p1DrawPileCurrentIndex]));
            break;
          case 'p1HandC5':
            p1HandC5Number = Math.floor(Number(shuffledDeck[p1DrawPileCurrentIndex]));
            break;
            }
        p1DrawPileCurrentIndex--;
        drawPileCount.innerHTML = p1DrawPileCurrentIndex + 1;
      }
      cardImages();
    }
    if (p1DrawPileCurrentIndex < 0) {
      drawPileCount.innerHTML = 0;
      // drawPileCount.style.v
      drawPile.style.backgroundImage = 'none';
    }
  }
  //pressing ' ; ' once, twice ...        index 42-31
  function stuckReset1() {
    playPile1.innerHTML = shuffledDeck[42];
    playPile2.innerHTML = shuffledDeck[41];
    playPile3.innerHTML = shuffledDeck[40];
    playPile4.innerHTML = shuffledDeck[39];
    playPile1Number = Math.floor(Number(playPile1.innerHTML));
    playPile2Number = Math.floor(Number(playPile2.innerHTML));
    playPile3Number = Math.floor(Number(playPile3.innerHTML));
    playPile4Number = Math.floor(Number(playPile4.innerHTML));
    cardImages();
  }
  function stuckReset2() {
    playPile1.innerHTML = shuffledDeck[38];
    playPile2.innerHTML = shuffledDeck[37];
    playPile3.innerHTML = shuffledDeck[36];
    playPile4.innerHTML = shuffledDeck[35];
    playPile1Number = Math.floor(Number(playPile1.innerHTML));
    playPile2Number = Math.floor(Number(playPile2.innerHTML));
    playPile3Number = Math.floor(Number(playPile3.innerHTML));
    playPile4Number = Math.floor(Number(playPile4.innerHTML));
    cardImages();
  }
  function stuckReset3() {
    playPile1.innerHTML = shuffledDeck[34];
    playPile2.innerHTML = shuffledDeck[33];
    playPile3.innerHTML = shuffledDeck[32];
    playPile4.innerHTML = shuffledDeck[31];
    playPile1Number = Math.floor(Number(playPile1.innerHTML));
    playPile2Number = Math.floor(Number(playPile2.innerHTML));
    playPile3Number = Math.floor(Number(playPile3.innerHTML));
    playPile4Number = Math.floor(Number(playPile4.innerHTML));
    cardImages();
  }
  // function stuckReset5()

  let semicolonPressCount = 1;
  document.addEventListener('keydown', function(event) {
    if (event.key === 'r' || event.key === 'R') {
      switch (semicolonPressCount) {
        case 1: 
          stuckReset1();
          semicolonPressCount++;
          neutralDrawPileCount.innerHTML = '2  <br>Resets';
          break;
        case 2: 
          stuckReset2();
          semicolonPressCount++;
          neutralDrawPileCount.innerHTML = '1  <br>Resets';
          break;
        case 3: 
          stuckReset3();
          semicolonPressCount++;
          neutralDrawPileCount.innerHTML = '';
          neutralDrawPile.style.backgroundImage = 'none';
          
          break;
        // case 5: 
        //   stuckReset5();
        //   semicolonPressCount++;
        //   break;
        default: 
        
      }
    }
  })



  function unFocusHand() {
    p1HandC1.classList.remove("focused");
    p1HandC1.classList.add("shadow-lg");
    p1HandC2.classList.remove("focused");
    p1HandC2.classList.add("shadow-lg");
    p1HandC3.classList.remove("focused");
    p1HandC3.classList.add("shadow-lg");
    p1HandC4.classList.remove("focused");
    p1HandC4.classList.add("shadow-lg");
    p1HandC5.classList.remove("focused");
    p1HandC5.classList.add("shadow-lg");

  }
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      firstDeal();
      cardImages();

      if (timer) {
        stopTimer();
      }
      resetTimer();
      startTimer();
    }
  })
  document.addEventListener('keydown', function(event) {
    if (event.key === 'a' || event.key === 'A') {
      unFocusHand();
      p1HandC1.classList.remove("shadow-lg");
      p1HandC1.classList.add("focused");
    }
    if (event.key === 's' || event.key === 'S') {
      unFocusHand();
      p1HandC2.classList.remove("shadow-lg");
      p1HandC2.classList.add("focused");
    }
    if (event.key === 'd' || event.key === 'D') {
      unFocusHand();
      p1HandC3.classList.remove("shadow-lg");
      p1HandC3.classList.add("focused");
    }
    if (event.key === 'f' || event.key === 'F') {
      unFocusHand();
      p1HandC4.classList.remove("shadow-lg");
      p1HandC4.classList.add("focused");
    }
    if (event.key === ' ' || event.key === 'Spacebar') {
      unFocusHand();
      p1HandC5.classList.remove("shadow-lg");
      p1HandC5.classList.add("focused");
    }
  })

  document.addEventListener('keydown', function(event) {
    if (event.key === 'j' || event.key === 'J') {
    if (p1HandC1.classList.contains("focused") && (p1HandC1Number === 1 && playPile1Number === 13 || p1HandC1Number === 13 && playPile1Number === 1)) {
      playPile1.innerHTML = p1HandC1.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC1.innerHTML = '';
      dealNextCard();
    } else if (p1HandC1.classList.contains("focused") && (p1HandC1Number === playPile1Number + 1 || p1HandC1Number === playPile1Number - 1)) {
      playPile1.innerHTML = p1HandC1.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC1.innerHTML = '';
      dealNextCard();
    }
    if (p1HandC2.classList.contains("focused") && (p1HandC2Number === 1 && playPile1Number === 13 || p1HandC2Number === 13 && playPile1Number === 1)) {
      playPile1.innerHTML = p1HandC2.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC2.innerHTML = '';
      dealNextCard();
    } else if (p1HandC2.classList.contains("focused") && (p1HandC2Number === playPile1Number + 1 || p1HandC2Number === playPile1Number - 1)) {
      playPile1.innerHTML = p1HandC2.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC2.innerHTML = '';
      dealNextCard();
    }
    if ((p1HandC3.classList.contains("focused") && (p1HandC3Number === 1 && playPile1Number === 13 || p1HandC3Number === 13 && playPile1Number === 1))) {
      playPile1.innerHTML = p1HandC3.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC3.innerHTML = '';
      dealNextCard();
    } else if (p1HandC3.classList.contains("focused") && (p1HandC3Number === playPile1Number + 1 || p1HandC3Number === playPile1Number - 1)) {
      playPile1.innerHTML = p1HandC3.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC3.innerHTML = '';
      dealNextCard();
    }
    if ((p1HandC4.classList.contains("focused") && (p1HandC4Number === 1 && playPile1Number === 13 || p1HandC4Number === 13 && playPile1Number === 1))) {
      playPile1.innerHTML = p1HandC4.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC4.innerHTML = '';
      dealNextCard();
    } else if (p1HandC4.classList.contains("focused") && (p1HandC4Number === playPile1Number + 1 || p1HandC4Number === playPile1Number - 1)) {
      playPile1.innerHTML = p1HandC4.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC4.innerHTML = '';
      dealNextCard();
    }
    if ((p1HandC5.classList.contains("focused") && (p1HandC5Number === 1 && playPile1Number === 13 || p1HandC5Number === 13 && playPile1Number === 1))) {
      playPile1.innerHTML = p1HandC5.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC5.innerHTML = '';
      dealNextCard();
    } else if (p1HandC5.classList.contains("focused") && (p1HandC5Number === playPile1Number + 1 || p1HandC5Number === playPile1Number - 1)) {
      playPile1.innerHTML = p1HandC5.innerHTML;
      playPile1Number = Math.floor(Number(playPile1.innerHTML));
      p1HandC5.innerHTML = '';
      dealNextCard();
    }
  }
    //next
    if (event.key === 'k' || event.key === 'K') {
      if (p1HandC1.classList.contains("focused") && (p1HandC1Number === 1 && playPile2Number === 13 || p1HandC1Number === 13 && playPile2Number === 1)) {
        playPile2.innerHTML = p1HandC1.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      } else if (p1HandC1.classList.contains("focused") && (p1HandC1Number === playPile2Number + 1 || p1HandC1Number === playPile2Number - 1)) {
        playPile2.innerHTML = p1HandC1.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      }
      if (p1HandC2.classList.contains("focused") && (p1HandC2Number === 1 && playPile2Number === 13 || p1HandC2Number === 13 && playPile2Number === 1)) {
        playPile2.innerHTML = p1HandC2.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      } else if (p1HandC2.classList.contains("focused") && (p1HandC2Number === playPile2Number + 1 || p1HandC2Number === playPile2Number - 1)) {
        playPile2.innerHTML = p1HandC2.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC3.classList.contains("focused") && (p1HandC3Number === 1 && playPile2Number === 13 || p1HandC3Number === 13 && playPile2Number === 1))) {
        playPile2.innerHTML = p1HandC3.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      } else if (p1HandC3.classList.contains("focused") && (p1HandC3Number === playPile2Number + 1 || p1HandC3Number === playPile2Number - 1)) {
        playPile2.innerHTML = p1HandC3.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC4.classList.contains("focused") && (p1HandC4Number === 1 && playPile2Number === 13 || p1HandC4Number === 13 && playPile2Number === 1))) {
        playPile2.innerHTML = p1HandC4.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      } else if (p1HandC4.classList.contains("focused") && (p1HandC4Number === playPile2Number + 1 || p1HandC4Number === playPile2Number - 1)) {
        playPile2.innerHTML = p1HandC4.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC5.classList.contains("focused") && (p1HandC5Number === 1 && playPile2Number === 13 || p1HandC5Number === 13 && playPile2Number === 1))) {
        playPile2.innerHTML = p1HandC5.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      } else if (p1HandC5.classList.contains("focused") && (p1HandC5Number === playPile2Number + 1 || p1HandC5Number === playPile2Number - 1)) {
        playPile2.innerHTML = p1HandC5.innerHTML;
        playPile2Number = Math.floor(Number(playPile2.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      }
    }
    if (event.key === 'l' || event.key === 'L') {
      if (p1HandC1.classList.contains("focused") && (p1HandC1Number === 1 && playPile3Number === 13 || p1HandC1Number === 13 && playPile3Number === 1)) {
        playPile3.innerHTML = p1HandC1.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      } else if (p1HandC1.classList.contains("focused") && (p1HandC1Number === playPile3Number + 1 || p1HandC1Number === playPile3Number - 1)) {
        playPile3.innerHTML = p1HandC1.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      }
      if (p1HandC2.classList.contains("focused") && (p1HandC2Number === 1 && playPile3Number === 13 || p1HandC2Number === 13 && playPile3Number === 1)) {
        playPile3.innerHTML = p1HandC2.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      } else if (p1HandC2.classList.contains("focused") && (p1HandC2Number === playPile3Number + 1 || p1HandC2Number === playPile3Number - 1)) {
        playPile3.innerHTML = p1HandC2.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC3.classList.contains("focused") && (p1HandC3Number === 1 && playPile3Number === 13 || p1HandC3Number === 13 && playPile3Number === 1))) {
        playPile3.innerHTML = p1HandC3.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      } else if (p1HandC3.classList.contains("focused") && (p1HandC3Number === playPile3Number + 1 || p1HandC3Number === playPile3Number - 1)) {
        playPile3.innerHTML = p1HandC3.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC4.classList.contains("focused") && (p1HandC4Number === 1 && playPile3Number === 13 || p1HandC4Number === 13 && playPile3Number === 1))) {
        playPile3.innerHTML = p1HandC4.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      } else if (p1HandC4.classList.contains("focused") && (p1HandC4Number === playPile3Number + 1 || p1HandC4Number === playPile3Number - 1)) {
        playPile3.innerHTML = p1HandC4.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC5.classList.contains("focused") && (p1HandC5Number === 1 && playPile3Number === 13 || p1HandC5Number === 13 && playPile3Number === 1))) {
        playPile3.innerHTML = p1HandC5.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      } else if (p1HandC5.classList.contains("focused") && (p1HandC5Number === playPile3Number + 1 || p1HandC5Number === playPile3Number - 1)) {
        playPile3.innerHTML = p1HandC5.innerHTML;
        playPile3Number = Math.floor(Number(playPile3.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      }
    }
    if (event.key === ';') {
      if (p1HandC1.classList.contains("focused") && (p1HandC1Number === 1 && playPile4Number === 13 || p1HandC1Number === 13 && playPile4Number === 1)) {
        playPile4.innerHTML = p1HandC1.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      } else if (p1HandC1.classList.contains("focused") && (p1HandC1Number === playPile4Number + 1 || p1HandC1Number === playPile4Number - 1)) {
        playPile4.innerHTML = p1HandC1.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC1.innerHTML = '';
        dealNextCard();
      }
      if (p1HandC2.classList.contains("focused") && (p1HandC2Number === 1 && playPile4Number === 13 || p1HandC2Number === 13 && playPile4Number === 1)) {
        playPile4.innerHTML = p1HandC2.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      } else if (p1HandC2.classList.contains("focused") && (p1HandC2Number === playPile4Number + 1 || p1HandC2Number === playPile4Number - 1)) {
        playPile4.innerHTML = p1HandC2.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC2.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC3.classList.contains("focused") && (p1HandC3Number === 1 && playPile4Number === 13 || p1HandC3Number === 13 && playPile4Number === 1))) {
        playPile4.innerHTML = p1HandC3.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      } else if (p1HandC3.classList.contains("focused") && (p1HandC3Number === playPile4Number + 1 || p1HandC3Number === playPile4Number - 1)) {
        playPile4.innerHTML = p1HandC3.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC3.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC4.classList.contains("focused") && (p1HandC4Number === 1 && playPile4Number === 13 || p1HandC4Number === 13 && playPile4Number === 1))) {
        playPile4.innerHTML = p1HandC4.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      } else if (p1HandC4.classList.contains("focused") && (p1HandC4Number === playPile4Number + 1 || p1HandC4Number === playPile4Number - 1)) {
        playPile4.innerHTML = p1HandC4.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC4.innerHTML = '';
        dealNextCard();
      }
      if ((p1HandC5.classList.contains("focused") && (p1HandC5Number === 1 && playPile4Number === 13 || p1HandC5Number === 13 && playPile4Number === 1))) {
        playPile4.innerHTML = p1HandC5.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      } else if (p1HandC5.classList.contains("focused") && (p1HandC5Number === playPile4Number + 1 || p1HandC5Number === playPile4Number - 1)) {
        playPile4.innerHTML = p1HandC5.innerHTML;
        playPile4Number = Math.floor(Number(playPile4.innerHTML));
        p1HandC5.innerHTML = '';
        dealNextCard();
      }
    }
});
//EXPIREMENTAL CODE FOR GLOBAL SCORES
function submitScore(event) {


  fetch('index.php', {
    method: 'post',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `scoreName=${encodeURIComponent(scoreName)}&score=${encodeURIComponent(score)}`
  })
  .then(response => response.text())
  .then(data => {
    document.open();
    document.write(data);
    document.close();
    loadScores();
  });
}

function loadScores() {
  const scores = <?php echo json_encode(get_scores()); ?>;

  topTen.innerHTML = '';
  scores.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `${item.scoreName}: ${item.score}`;
    topTen.appendChild(li);
  });
}
document.addEventListener('DOMContentLoaded', loadScores);


  
</script>
</body>
</html>
