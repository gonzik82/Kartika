
// /////////
// api.js
// ////////


const checkStatus = (response) => {
  if (response.status >= 200 && response.status < 300) {
    return response;
  } else {
    throw new Error(`${response.status}: ${response.statusText}`);
  }
};

export default class API {
  constructor({
    endPoint,
    authorization
  }) {
    this._endPoint = endPoint;
    this._authorization = authorization;
  }

  getTasks() {
    return this._load({
      url: `movies`
    })
      .then(toJSON)
      .then(ModelTask.parseTasks);
  }

  createTask({
    task
  }) {
    return this._load({
      url: `tasks`,
      method: Method.POST,
      body: JSON.stringify(task),
      headers: new Headers({
        'Content-Type': `application/json`
      })
    })
      .then(toJSON);
  }

  updateTask({
    id,
    data
  }) {
    return this._load({
      url: `movies/${id}`,
      method: Method.PUT,
      body: JSON.stringify(data),
      headers: new Headers({
        'Content-Type': `application/json`
      })
    })
      .then(toJSON)
      .then(ModelTask.parseTask);
  }

  deleteTask({
    id
  }) {
    return this._load({
      url: `tasks/${id}`,
      method: Method.DELETE
    });
  }

  _load({
    url,
    method = Method.GET,
    body = null,
    headers = new Headers()
  }) {
    headers.append(`Authorization`, this._authorization);

    return fetch(`${this._endPoint}/${url}`, {
      method,
      body,
      headers
    })
      .then(checkStatus)
      .catch((err) => {
        throw err;
      });
  }
}
