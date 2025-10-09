/* variavel: é como uma caixa que serve para 
armazenar informações. é um espaço no programa/computador que armazena dados.

*/



//pode mudar o valor dentro do código
let nomevariavel=1;//inteiro
let nomevariavel2="gabirela"//varchar
let nomevariavel13=2.7;//double
let nomevariavel14=true ;//boolean

//váriavel constante, que não altera o valor

const nome ="gabriela";

// operações matemáticas 

let soma = 3+5; //8
let subtracao= 5-3; //2
let multiplicacao =3*5; // 15
let divisão = 10/2; // 5


// juntar textos

let primeironome = "Maria";
let sobrenome = "Reis";
let nomecompleto = primeironome + sobrenome;

// funções 
// função  sem parametro: que não recebe dados dentro do ()
 // função ela imprime o Olá mundo
function imprimirMsg(){
// console é utilizado para mostrar textos
console.log("Hello Word!");
console.log(primeironome+ "Bem Vinda!");

}

function subtrairValores(valor1,valor2){
let sub = valor1+valor1;
console.log("O Resultado da soma é:"+subtrair);

}
imprimirMsg();
somarValores(20,40);
subtrairValores(100,10);

function imc(altura,peso,nomepessoa){
let resultado =(altura/peso) * altura;
console.log(nomepessoa+ "o seu IMC é:"+resultado);

}

imc(1.80,70,"Rhauan");

// condicional
/*
É uma ação que é executada com base em um critério
- se chover irei ao cinema, se fizer sol irei á praia 

-hoje choveu!(ir ao cinema)
-hoje fez sol! (ir á praia)

Se fizer sol e eu tiver dinheiro, irei á praia
senão ficarei em casa.
-Fez sol e tenho dinheiro (irei á praia)
-Fez sol mas eu não tenho dinheiro(casa)
-Choveu mas eu tenho dinheiro (casa)
*/

let n1 =15;
let n2 = 45;
//if - SE else - Senão

// se n1 for igual a 10
if(n1=10){
    console.log("Irei á praia!");
}else{
    console.log("fico em casa!");
}

// se n1 for maior que 10 e n2 for menor que 40
if(n1>10 & n2<40){
console.log("Irei á praia!");
}else{
    console.log("Fico em casa!");
}

if(n1>10 & n2<40){
console.log("Irei á praia!");
}else{
    console.log("Fico em casa!");
}

// if aninhado
// se n1 é maior que 12 e n2 maior que 48
if(n1>12 & n2>48) {
    console.log ("Irei á praia");
    // se n1 é maior ou igual a 15 e n2 menor que 45
}else
    if(n1>=15 & n2<45){
        console.log ("Vou ao cinema!");
        /* se n1 é maior que 14 e n2 igual a 45

        se n2 for maior que n1 OU n1 maior pu igual a 15
        */
    }else if((n1>14 && n2==)) (n2>n1 || n1>=15)




    // OBJETO CARRO
    let carro ={
cor: "preto",
placa:"KJH9876",
modelo:"fusca",
kmRodados: 120000,
som:"true",
arcondicionado: false
 };
 console.log(carro.cor+carro.modelo+carro.placa);

 