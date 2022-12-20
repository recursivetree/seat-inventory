async function jsonPostAction(url, data) {
    return await fetch(url, {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify(data),
    })
}