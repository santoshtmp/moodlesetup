// javascript for all pages
document.addEventListener("DOMContentLoaded", function () {
  /**
   * -------------- certificcate page --------------
   */
  const certificateImage = document.querySelectorAll(".element");
  let textBubble = document.createElement("div");
  textBubble.id = "add_course_text_bubble";
  const handleChange = () => {
    textBubble.textContent = existingElement.value;
    if (existingElement.value.length === 0) {
      textBubble.style.display = "none";
    } else {
      textBubble.style.display = "block";
    }
  };
  for (let i = 0; i < certificateImage.length; i++) {
    certificateImage[i].addEventListener("click", (event) => {
      event.stopPropagation();
    });
  }
  // Find the existing element
  let existingElement = document.getElementById(
    "id_customfield_course_summary_for_certificate"
  );
  if (existingElement) {
    if (existingElement.value.length === 0) {
      textBubble.style.display = "none";
    } else {
      textBubble.style.display = "block";
    }
    textBubble.textContent = existingElement.value;
    existingElement.addEventListener("change", handleChange);
    // Insert the new sibling element after the existing element
    existingElement.parentNode.insertBefore(
      textBubble,
      existingElement.nextSibling
    );
  }
  /**
   * -------------------------------------------------
   */

});
