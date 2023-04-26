await (await fetch("/flux-ilias-rest-api/organisational-unit-positions")).json();

const time = Date.now();
const organisational_unit_position = await (await fetch("/flux-ilias-rest-api/organisational-unit-position/create", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify({
        authorities: [
            {
                over_position_id: (await (await fetch("/flux-ilias-rest-api/organisational-unit-position/by-core-identifier/employee")).json()).id,
                scope_in: "same_and_subsequent"
            }
        ],
        title: `Organisational unit position ${time}`
    })
})).json();

await (await fetch(`/flux-ilias-rest-api/organisational-unit-position/by-id/${organisational_unit_position.id}`)).json();

await (await fetch(`/flux-ilias-rest-api/organisational-unit-position/by-id/${organisational_unit_position.id}/update`, {
    method: "PATCH",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify({
        description: "Some description of the organisational unit position"
    })
})).json();

await (await fetch(`/flux-ilias-rest-api/organisational-unit-position/by-id/${organisational_unit_position.id}/delete`, {
    method: "DELETE"
})).json();
