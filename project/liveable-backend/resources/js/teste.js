let rota = 'http://localhost:8000/api';

async function readRoute(url) {
    let resp = await fetch(url);

    console.log('STATUS:', resp.status);

    let data = await resp.json();

    console.log(data);
}

readRoute(rota);
