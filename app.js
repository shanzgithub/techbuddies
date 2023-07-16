$(document).ready(function() {
  $('#searchForm').on('submit', function(event) {
    event.preventDefault();
    const searchInput = $('#searchInput').val().toLowerCase();
    filterRecipes(searchInput);
  });

  // Function to filter recipes based on search keyword
  function filterRecipes(keyword) {
    const recipeCards = $('.recipeCard');
    recipeCards.each(function() {
      const recipeName = $(this).find('h3').text().toLowerCase();
      const recipeIngredients = $(this).find('.ingredients').text().toLowerCase();
      const recipeInstructions = $(this).find('.instructions').text().toLowerCase();

      if (recipeName.includes(keyword) || recipeIngredients.includes(keyword) || recipeInstructions.includes(keyword)) {
        $(this).show(); // Show the recipe card
      } else {
        $(this).hide(); // Hide the recipe card
      }
    });
  }

  // Function to generate recipe cards from data
  function generateRecipeCards(recipes) {
    const recipeCardsContainer = $('#recipeCards');
    recipeCardsContainer.empty(); // Clear existing recipe cards
  
    recipes.forEach(recipe => {
      const recipeCard = $('<div>').addClass('recipeCard');
  
      const title = $('<h3>').text(recipe.name);
  
      const image = $('<img>').attr('src', recipe.image).attr('alt', recipe.name);
  
      const description = $('<p>').text(recipe.description);
  
      const ingredients = $('<div>').addClass('ingredients');
      const ingredientsTitle = $('<h4>').text('Ingredients');
      ingredients.append(ingredientsTitle);
      const ingredientsList = $('<ul>');
  
      // Check if ingredients is an array
      if (Array.isArray(recipe.ingredients)) {
        recipe.ingredients.forEach(ingredient => {
          const ingredientItem = $('<li>').text(ingredient);
          ingredientsList.append(ingredientItem);
        });
      }
  
      ingredients.append(ingredientsList);
  
      recipeCard.append(title);
      recipeCard.append(image);
      recipeCard.append(description);
      recipeCard.append(ingredients);
  
      recipeCardsContainer.append(recipeCard);
    });
  }

  // Function to fetch recipes from the server
  function fetchRecipes() {
    $.ajax({
      url: 'api.php',
      method: 'GET',
      dataType: 'json',
      success: function(data) {
        if (data && Array.isArray(data)) {
          generateRecipeCards(data);
        } else {
          console.error('Invalid response data:', data);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error fetching recipe data:', error);
      }
    });
  }
  

  // Function to create a recipe
  function createRecipe(recipeData) {
    $.ajax({
      url: 'api.php',
      method: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify(recipeData),
      success: function(recipe) {
        if (recipe && typeof recipe === 'object') {
          addRecipeCard(recipe); // Add the new recipe card to the UI
        } else {
          console.error('Invalid response data');
        }
      },
      error: function(error) {
        console.error('Error creating recipe:', error);
      }
    });
  }

  // Function to add a recipe card to the UI
  function addRecipeCard(recipe) {
    const recipeCard = $('<div>').addClass('recipeCard');

    const title = $('<h3>').text(recipe.name);

    const image = $('<img>').attr('src', recipe.image).attr('alt', recipe.name);

    const description = $('<p>').text(recipe.description);

    const ingredients = $('<div>').addClass('ingredients');
    const ingredientsTitle = $('<h4>').text('Ingredients');
    ingredients.append(ingredientsTitle);
    const ingredientsList = $('<ul>');
    recipe.ingredients.forEach(ingredient => {
      const ingredientItem = $('<li>').text(ingredient);
      ingredientsList.append(ingredientItem);
    });
    ingredients.append(ingredientsList);

    recipeCard.append(title);
    recipeCard.append(image);
    recipeCard.append(description);
    recipeCard.append(ingredients);

    $('#recipeCards').append(recipeCard);
  }

  // Function to fetch recipes when the page loads
  fetchRecipes();
});
