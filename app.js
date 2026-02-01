const form = document.querySelector('#taskForm');
const input = document.querySelector('#taskInput');
const list = document.querySelector('#taskList');
const emptyState = document.querySelector('#emptyState');
const count = document.querySelector('#taskCount');
const clearDone = document.querySelector('#clearDone');
const filterButtons = Array.from(document.querySelectorAll('.filter'));

const STORAGE_KEY = 'planner.tasks';

let tasks = loadTasks();
let activeFilter = 'all';

function loadTasks() {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (!saved) {
    return [];
  }
  try {
    const parsed = JSON.parse(saved);
    if (Array.isArray(parsed)) {
      return parsed;
    }
  } catch (error) {
    console.error('Nie udało się wczytać zadań', error);
  }
  return [];
}

function saveTasks() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
}

function render() {
  list.innerHTML = '';
  const visibleTasks = tasks.filter((task) => {
    if (activeFilter === 'active') {
      return !task.done;
    }
    if (activeFilter === 'done') {
      return task.done;
    }
    return true;
  });

  visibleTasks.forEach((task) => {
    const item = document.createElement('li');
    item.className = `task ${task.done ? 'is-done' : ''}`;
    item.dataset.id = task.id;
    item.setAttribute('role', 'button');
    item.setAttribute('tabindex', '0');

    const content = document.createElement('div');
    const title = document.createElement('div');
    title.className = 'task__title';
    title.textContent = task.title;

    const meta = document.createElement('div');
    meta.className = 'task__meta';
    meta.textContent = task.done ? 'Zrobione' : 'Do zrobienia';

    content.append(title, meta);

    const badge = document.createElement('span');
    badge.className = 'task__badge';
    badge.textContent = task.done ? '✓ Zakończone' : '• W toku';

    item.append(content, badge);
    item.addEventListener('click', () => toggleTask(task.id));
    item.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleTask(task.id);
      }
    });

    list.append(item);
  });

  emptyState.style.display = visibleTasks.length ? 'none' : 'block';
  updateCount();
}

function updateCount() {
  const activeCount = tasks.filter((task) => !task.done).length;
  count.textContent = activeCount;
}

function addTask(title) {
  tasks.unshift({
    id: crypto.randomUUID(),
    title,
    done: false,
  });
  saveTasks();
  render();
}

function toggleTask(id) {
  tasks = tasks.map((task) =>
    task.id === id ? { ...task, done: !task.done } : task
  );
  saveTasks();
  render();
}

function clearCompleted() {
  tasks = tasks.filter((task) => !task.done);
  saveTasks();
  render();
}

function setFilter(filter) {
  activeFilter = filter;
  filterButtons.forEach((button) => {
    button.classList.toggle('is-active', button.dataset.filter === filter);
  });
  render();
}

form.addEventListener('submit', (event) => {
  event.preventDefault();
  const title = input.value.trim();
  if (!title) {
    return;
  }
  addTask(title);
  input.value = '';
  input.focus();
});

clearDone.addEventListener('click', clearCompleted);
filterButtons.forEach((button) => {
  button.addEventListener('click', () => setFilter(button.dataset.filter));
});

render();
