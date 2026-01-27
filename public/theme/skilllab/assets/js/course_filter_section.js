// 
document.addEventListener("DOMContentLoaded", function () {
  // Your code here
  const closeFilter = () => {
    filter.classList.add("hide");
  };
  const handleFilterToggle = (event) => {
    filter.classList.toggle("hide");
    event.stopPropagation();
  };

  const handleCategoryToggle = (event) => {
    category.classList.toggle("hide");
    event.stopPropagation();
  };
  const handleSkillToggle = (event) => {
    skillLevel.classList.toggle("hide");
    event.stopPropagation();
  };
  const handleDateToggle = (event) => {
    date.classList.toggle("hide");
    event.stopPropagation();
  };

  document.addEventListener("click", closeFilter);

  const filter = document.querySelector(".filter-options-wrapper");
  const category = document.querySelector(".category");
  const categoryToggle = document.querySelector(".category-toggle");
  const skillLevel = document.querySelector(".skill-level");
  const skillLevelToggle = document.querySelector(".skill-toggle");
  const date = document.querySelector(".date");
  const dateToggle = document.querySelector(".date-toggle");
  const filterButton = document.querySelector(".filter-btn");
  const submitButton = document.querySelector("#filter-submit");
  const clearButton = document.querySelector("#filter-clear");

  if (filter) {
    filter.addEventListener("click", (event) => {
      event.stopPropagation();
    });
    clearButton.addEventListener("click", closeFilter);
    submitButton.addEventListener("click", closeFilter);

    filterButton.addEventListener("click", handleFilterToggle);
    categoryToggle.addEventListener("click", handleCategoryToggle);
    skillLevelToggle.addEventListener("click", handleSkillToggle);
    dateToggle.addEventListener("click", handleDateToggle);
  }
});
