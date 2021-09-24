const postPlayerProgress = (
  localStorageKey,
  localStorageKeyLastUpdate,
  serverTracking = false
) => {
  const playerStorage = JSON.parse(localStorage.getItem(localStorageKey));
  const playerStorageLastUpdate = JSON.parse(
    localStorage.getItem(localStorageKeyLastUpdate)
  );

  if (
    serverTracking &&
    playerStorage != null &&
    playerStorageLastUpdate?.percent !== playerStorage?.percent
  ) {
    fetch(config.server_api_post_progress, {
      method: "POST",
      body: JSON.stringify(playerStorage),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((result) => {
        localStorage.setItem(
          localStorageKeyLastUpdate,
          JSON.stringify(playerStorage)
        );
      })
      .catch((error) => console.log(error));
  }
};

export default postPlayerProgress;
