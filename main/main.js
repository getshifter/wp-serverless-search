/**
 * WP Serverless Search
 * A static search plugin for WordPress.
 */


(function(){

  var index = new FlexSearch({
      encode: "advanced",
      tokenize: "reverse",
      suggest: true,
      cache: true
  });

  for(var i = 0; i < data.length; i++){
    index.add(i, data[i]);
  }

  var suggestions = document.getElementById("suggestions");
  var autocomplete = document.getElementById("autocomplete");
  var userinput = document.getElementById("userinput");

  userinput.addEventListener("input", show_results, true);
  userinput.addEventListener("keyup", accept_autocomplete, true);
  suggestions.addEventListener("click", accept_suggestion, true);

  function show_results(){

      var value = this.value;
      var results = index.search(value, 25);
      var entry, childs = suggestions.childNodes;
      var i = 0, len = results.length;

      for(; i < len; i++){

          entry = childs[i];

          console.log(entry)

          if(!entry){

              entry = document.createElement("div");
              suggestions.appendChild(entry);
          }

          entry.textContent = data[results[i]];
      }

      while(childs.length > len){
        suggestions.removeChild(childs[i])
      }

      var first_result = data[results[0]];
      var match = first_result && first_result.toLowerCase().indexOf(value.toLowerCase());

      if(first_result && (match !== -1)){

          autocomplete.value = value + first_result.substring(match + value.length);
          autocomplete.current = first_result;
      }
      else{

          autocomplete.value = autocomplete.current = value;
      }
  }

  function accept_autocomplete(event){

      if((event || window.event).keyCode === 13) {

          this.value = autocomplete.value = autocomplete.current;
      }
  }

  function accept_suggestion(event){

      var target = (event || window.event).target;

      userinput.value = autocomplete.value = target.textContent;

      while(suggestions.lastChild){

          suggestions.removeChild(suggestions.lastChild);
      }

      return false;
  }
}());