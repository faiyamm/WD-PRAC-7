document.addEventListener("DOMContentLoaded", () => {
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

    let NASA_API_KEY = "aEqr0YIh7aywtOdqgXCk01tKDw6fN1zu7emhbLpW"
    let NASA_APOD_URL = `https://api.nasa.gov/planetary/apod?api_key=${NASA_API_KEY}`

    let currentApod = null
    let isLoggedIn = false
    let currentUser = null

    function checkAuthStatus() {
        fetch("../backend/auth.php?action=check_auth") // Fixed: Added missing /
            .then((res) => res.json())
            .then((data) => {
                isLoggedIn = data.isLoggedIn
                currentUser = data.user // Fixed: Changed from currentUser to user
                updateAuthUI()

                favoriteBtn.disabled = !isLoggedIn // Fixed: Fixed spacing
                if(!isLoggedIn){
                    favoriteBtn.title = "Please login to add favorites"
                }
                if(isLoggedIn){
                    loadFavorites()
                } else {
                    favoritesList.innerHTML = '<p class="no-favorites">Please log in to view your favorites.</p>'
                }
            })
            .catch((err) => {
                console.error("Error checking auth status:", err)
            })
    }

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

    function logout(){
        fetch("../backend/auth.php?action=logout") // Fixed: Added missing /
        .then((res) => res.json())
        .then((data) => {
            if (data.success){
                isLoggedIn = false
                currentUser = null
                updateAuthUI()
                favoriteBtn.disabled = true
                favoritesList.innerHTML = '<p class="no-favorites">Please log in to view your favorites.</p>'
            }
        })
        .catch((err) => {
            console.error("Error logging out:", err)
        })
    }

    async function fetchAPOD(){
        try{
            showLoader()
            let res = await fetch(NASA_APOD_URL)
            if(!res.ok){
                throw new Error(`HTTP Error! Status: ${res.status}`)
            }
            let data = await res.json()
            currentApod = data
            displayAPOD(data)
        } catch (err) {
            console.error("Error fetching APOD:", err)
            showError("An error occurred while fetching the Astronomy Picture of the Day. Please try again later.")
        }
    }

    function displayAPOD(data){
        apodTitle.textContent = data.title
        if(data.media_type === "image"){
            apodImage.src = data.url
            apodImage.style.display = "block"
        } else if (data.media_type === "video"){
            apodImage.src = "/placeholder.svg?height=400&width=600"
            apodImage.style.display = "block"
            apodExplanation.innerHTML =
            `<p>Today's astronomy content is a video. <a href="${data.url}" target="_blank">Click here to view it</a>.</p>` +
            data.explanation
        }
        let dateObj = new Date(data.date)
        let formattedDate = dateObj.toLocaleDateString("en-US", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        })

        apodDate.textContent = formattedDate
        apodExplanation.textContent = data.explanation
        hideLoader()
    }

    function addToFavorites(){
        if(!currentApod || !isLoggedIn) return

        let favoriteData = {
            title: currentApod.title,
            date: currentApod.date,
            explanation: currentApod.explanation.substring(0, 150) + "...",
            url: currentApod.url,
            media_type: currentApod.media_type,
        }

        fetch("../backend/main.php", { // Fixed: Added missing /
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
            if(data.success){
                alert("Added to favorites!")
                loadFavorites()
            } else {
                alert("Failed to add to favorites. " + data.message) // Fixed: Added space
            }
        })
        .catch((err) => {
            console.error("Error adding to favorites:", err)
            alert("An error occurred while adding to favorites. Please try again later.")
        })
    }

    function loadFavorites(){
        if (!isLoggedIn) return

        fetch("../backend/main.php?action=get_favorites") // Fixed: Added missing /
        .then((res) => res.json())
        .then((data) => {
            if(data.success){
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
            favoritesList.innerHTML = '<p class="no-favorites">You have no favorites yet. Add some!</p>'
            return
        }
        favoritesList.innerHTML = ""
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
    favoriteBtn.addEventListener("click", addToFavorites)
    checkAuthStatus()
    fetchAPOD()

})