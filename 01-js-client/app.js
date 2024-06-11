const res = await fetch("http://localhost:8000");
const users = await res.json();
console.log(users);

// div id="users"
const usersDiv = document.querySelector("#users");

// Pour chaque user
users.forEach((u) => {
  // Je crée une balise p
  const p = document.createElement("p");
  // J'inscris en tant que texte le nom et le prénom de l'utilisateur
  p.innerText = `${u.name} ${u.firstname}`;
  // Je rajoute la balise p à l'intérieur de la div des users
  usersDiv.appendChild(p);
});
