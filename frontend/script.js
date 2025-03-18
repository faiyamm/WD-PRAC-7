document.addEventListener("DOMContentLoaded", () => {
    //set variables
    let loader = document.getElementById("loader")
    let apodContent = document.getElementById("apod-content") // Fixed: Changed from apodContent to apod-content
    let apodTitle = document.getElementById("apod-title")
    let apodImage = document.getElementById("apod-image")
    let apodDate = document.getElementById("apod-date")
    let apodExplanation = document.getElementById("apod-explanation")
    let favoriteBtn = document.getElementById("favorite-btn")
    let errorMessage = document.getElementById("error-message")
    let favoritesList = document.getElementById("favorites-list")
    let authContainer = document.getElementById("auth-container")
    // nasa api connection
    let NASA_API_KEY = "aEqr0YIh7aywtOdqgXCk01tKDw6fN1zu7emhbLpW"
    let NASA_APOD_URL = `https://api.nasa.gov/planetary/apod?api_key=${NASA_API_KEY}`
    // curent apod data before fetching
    let currentApod = null
    let isLoggedIn = false
    let currentUser = null

    // function to check auth status
    function checkAuthStatus() {
        fetch("../backend/auth.php?action=check_auth") // Fixed: Added missing /
            .then((res) => res.json()) 
            .then((data) => {
                isLoggedIn = data.isLoggedIn
                currentUser = data.user // Fixed: Changed from currentUser to user
                updateAuthUI() // if user is logged in, update the UI
                
                favoriteBtn.disabled = !isLoggedIn // Fixed: Fixed spacing
                if(!isLoggedIn){
                    favoriteBtn.title = "Please login to add favorites"
                }
                if(isLoggedIn){ // if logged in, shows user name and enables favorites
                    loadFavorites()
                } else {
                    favoritesList.innerHTML = '<p class="no-favorites">Please log in to view your favorites.</p>'
                }
            })
            .catch((err) => {
                console.error("Error checking auth status:", err)
            })
    }
    // function to update auth UI based on login status
    function updateAuthUI(){
        if(isLoggedIn && currentUser){ 
            authContainer.innerHTML = `
            <div class="user-info">
                <span class="welcome-message">Welcome, ${currentUser.username}!</span>
                <button class="logout-btn" id="logout-btn">Logout</button>
            </div>
            `
            document.getElementById("logout-btn").addEventListener("click", logout)
        } else {
            authContainer.innerHTML = `
            <div class="auth-links">
                <a href="../backend/login.php" class="auth-link">Login</a>
                <a href="../backend/register.php" class="auth-link">Register</a>
            </div>
            `
        }
    }

    function logout(){ // sends a logout request to the server
        fetch("../backend/auth.php?action=logout") // sends a logout request to the server
        .then((res) => res.json())
        .then((data) => {
            if (data.success){ // clears your user information
                isLoggedIn = false 
                currentUser = null
                updateAuthUI() // updates the UI again
                favoriteBtn.disabled = true
                favoritesList.innerHTML = '<p class="no-favorites">Please log in to view your favorites.</p>'
            }
        })
        .catch((err) => {
            console.error("Error logging out:", err)
        })
    }
    // fetch apod data
    async function fetchAPOD(){
        try{
            showLoader() // shows the loader while fetching
            let res = await fetch(NASA_APOD_URL) // it asks for the data from the NASA API
            if(!res.ok){ // if the response is not ok, it throws an error
                throw new Error(`HTTP Error! Status: ${res.status}`)
            }
            let data = await res.json() // if the response is ok, it parses the data
            currentApod = data
            displayAPOD(data) // displays the data
        } catch (err) { // if there is an error, it shows an error message
            console.error("Error fetching APOD:", err)
            showError("An error occurred while fetching the Astronomy Picture of the Day. Please try again later.")
        }
    }
    // function to display apod data
    function displayAPOD(data){
        apodTitle.textContent = data.title // displays the title
        if(data.media_type === "image"){ // if the media type is an image, it displays the image
            apodImage.src = data.url
            apodImage.style.display = "block"
        } else if (data.media_type === "video"){ // if the media type is a video, it displays a placeholder image
            apodImage.src = "/placeholder.svg?height=400&width=600"
            apodImage.style.display = "block"
            apodExplanation.innerHTML =
            `<p>Today's astronomy content is a video. <a href="${data.url}" target="_blank">Click here to view it</a>.</p>` +
            data.explanation
        }
        let dateObj = new Date(data.date) // displays the date
        let formattedDate = dateObj.toLocaleDateString("en-US", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        })

        apodDate.textContent = formattedDate 
        apodExplanation.textContent = data.explanation
        hideLoader() // hides the loader
    }
    // function to add to favorites
    function addToFavorites(){
        if(!currentApod || !isLoggedIn) return // check if there is an APOD and if the user is logged in, if not, it returns

        let favoriteData = { // creates an object with the APOD data
            title: currentApod.title,
            date: currentApod.date,
            explanation: currentApod.explanation.substring(0, 150) + "...", // shortens the explanation to 150 characters, nobody wanna read that much lol
            url: currentApod.url,
            media_type: currentApod.media_type,
        }
        //sends the data to the server to save in user favorites
        fetch("../backend/main.php", { // Fixed: Added missing /  im just a human lol
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "add_favorite",
                data: favoriteData // Fixed: Changed key from favorite to data to match PHP expectation
            }),
        })
        .then((res) => res.json())
        .then((data) => {
            if(data.success){ // if the data is saved successfully, it shows a message and loads the favorites
                alert("Added to favorites!")
                loadFavorites()
            } else { // if there is an error, it shows an error message
                alert("Failed to add to favorites. " + data.message) // Fixed: Added space
            }
        }) 
        .catch((err) => { 
            console.error("Error adding to favorites:", err)
            alert("An error occurred while adding to favorites. Please try again later.")
        })
    }

    function loadFavorites(){
        if (!isLoggedIn) return // check if there is an APOD and if the user is logged in, if not, it returns
        // asks the server for the user favorites
        fetch("../backend/main.php?action=get_favorites") // Fixed: Added missing /
        .then((res) => res.json())
        .then((data) => {
            if(data.success){ // if the data is loaded successfully, it displays the favorites
                displayFavorites(data.data) // Fixed: Changed from favorites to data to match PHP response
            } else {
                console.error("Error loading favorites:", data.message)
            }
        })
        .catch((err) => {
            console.error("Error loading favorites:", err)
        })
    }

    function displayFavorites(favorites){
        if(!favorites || favorites.length === 0){ // Fixed: Added null check
            favoritesList.innerHTML = '<p class="no-favorites">You have no favorites yet. Add some!</p>' // shows a message if there are no favorites
            return
        }
        favoritesList.innerHTML = "" // clears the list and creates a card for each favorite
        favorites.forEach((favorite) => { // Fixed: Changed variable name from favorites to favorite
            let favoriteItem = document.createElement("div")
            favoriteItem.className = "favorite-item"
            let dateObj = new Date(favorite.date)
            let formattedDate = dateObj.toLocaleDateString("en-US", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric"
            })

            favoriteItem.innerHTML = `
                <h3>${favorite.title}</h3>
                <p class="date">${formattedDate}</p>
                <p>${favorite.explanation}</p>
            `
            favoritesList.appendChild(favoriteItem)
        })
    }
    //small functions that control what's visible on the page
    function showLoader(){
        loader.style.display = "flex"
        apodContent.style.display = "none"
        errorMessage.style.display = "none"
    }
    function hideLoader(){
        loader.style.display = "none"
        apodContent.style.display = "block"
    }
    function showError(message){
        loader.style.display = "none"
        apodContent.style.display = "none"
        errorMessage.style.display = "block"
        errorMessage.textContent = message
    }
    favoriteBtn.addEventListener("click", addToFavorites) // sets the event listener for the favorite button
    checkAuthStatus() // checks the auth status when the page loads
    fetchAPOD() // fetches the APOD when the page loads
})