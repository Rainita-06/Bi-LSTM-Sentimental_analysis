<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php'; // Include database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentiment Analysis</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .result-container {
            display: none;
            margin-top: 20px;
        }
        .bar {
            height: 20px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sentiment Analysis</h1>
        <textarea id="tweetInput" placeholder="Enter your tweet here..."></textarea>
        <button onclick="analyzeTweet()">Analyze</button>
        <div id="result" class="result-container">
            <h2 id="sentimentText"></h2>
            <div id="sentimentBar"></div>
        </div>
        <a href="logout.php">Logout</a>
    </div>

    <script>
        async function analyzeTweet() {
            const tweet = document.getElementById("tweetInput").value.trim();

            if (tweet === "") {
                alert("Please enter a tweet to analyze.");
                return;
            }

            let analysisData;

            // Analyze using percentage logic
            const percentageResult = analyzePercentage(tweet);
            if (percentageResult) {
                analysisData = percentageResult;
                displayResult(analysisData);
            } else {
                // Fallback to polarity analysis if percentage logic doesn't apply
                analysisData = analyzePolarity(tweet);
                displayResult(analysisData);
            }

            // Log the analysis result to the server
            await fetch('log_analysis.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tweet, ...analysisData })
            });
        }

      
// Function to handle percentage-based tweets
function analyzePercentage(tweet) {
    const percentages = tweet.match(/(\d+)%/g); // Look for percentages in the tweet

    if (percentages && percentages.length >= 2) {
        const positive = parseInt(percentages[0].replace('%', ''));
        const negative = parseInt(percentages[1].replace('%', ''));

        let sentiment = '';
        let recommendation = '';

        if (positive > negative) {
            sentiment = 'positive';
            recommendation = "Recommended to buy";
        } else if (negative > positive) {
            sentiment = 'negative';
            recommendation = "Not recommended to buy";
        } else {
            sentiment = 'neutral';
            recommendation = "Neutral sentiment";
        }

        return { sentiment, recommendation, confidence: 100 };
    }

    return null;
}

// Function for single-tweet sentiment analysis using polarity logic
function analyzePolarity(tweet) {
    // Extended positive and negative word lists
    const positivePolarity = [
        "good", "great", "amazing", "love", "positive", "excellent", "awesome", "fantastic", "happy", "wonderful",
        "delightful", "outstanding", "incredible", "satisfying", "pleasing", "superb", "brilliant", "fabulous"
    ];
    const negativePolarity = [
        "bad", "terrible", "worst", "hate", "negative", "poor", "awful", "disappointing", "horrible", "ugly",
        "sad", "unpleasant", "dreadful", "mediocre", "unsatisfactory", "lame", "boring", "regret"
    ];
    let polarity = 0;

    positivePolarity.forEach(word => {
        if (tweet.toLowerCase().includes(word)) polarity += 0.2;
    });

    negativePolarity.forEach(word => {
        if (tweet.toLowerCase().includes(word)) polarity -= 0.2;
    });

    let recommendation = '';
    if (polarity >= 0.05) {
        recommendation = "Recommended to Buy the Product";
    } else if (polarity <= -0.05) {
        recommendation = "Not Recommended to Buy the Product";
    } else {
        recommendation = "Neutral Sentiment - Consider More Research";
    }

    return { sentiment: polarity > 0 ? 'positive' : polarity < 0 ? 'negative' : 'neutral', recommendation, confidence: Math.abs(polarity) * 100 };
}

// Function to display percentage-based analysis results
function displayPercentageResult({ sentiment, recommendation, confidence }) {
    const resultText = `${recommendation} (Confidence: ${confidence}%)`;
    displayResult({ sentiment, recommendation: resultText, confidence });
}

// Function to display sentiment analysis results
function displayResult({ sentiment, recommendation, confidence }) {
    const sentimentText = document.getElementById("sentimentText");
    const sentimentBar = document.getElementById("sentimentBar");

    // Set the sentiment text
    sentimentText.innerText = recommendation;

    // Create the sentiment bar
    const sentimentBarElement = document.createElement("div");
    sentimentBarElement.classList.add("bar");

    // Color based on sentiment
    if (sentiment === 'positive') {
        sentimentBarElement.style.backgroundColor = 'green';
        sentimentBarElement.style.width = `${confidence}%`;
    } else if (sentiment === 'negative') {
        sentimentBarElement.style.backgroundColor = 'red';
        sentimentBarElement.style.width = `${confidence}%`;
    } else {
        sentimentBarElement.style.backgroundColor = 'orange';
        sentimentBarElement.style.width = `${confidence}%`;
    }

    // Clear previous content and append the new bar
    sentimentBar.innerHTML = '';
    sentimentBar.appendChild(sentimentBarElement);

    // Show the result container
    document.querySelector(".result-container").style.display = 'block';
}


    </script>
</body>
</html>
