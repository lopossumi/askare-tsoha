CREATE TABLE Human(
    id          SERIAL          PRIMARY KEY,
    username    varchar(20)     NOT NULL, 
    fullname    varchar(100)    NOT NULL,
    password    varchar(50)     NOT NULL,
    email       varchar(100)    NOT NULL,
    isprivate   boolean         DEFAULT TRUE,
    isadmin     boolean         DEFAULT FALSE
);

CREATE TABLE TaskList(
    id          SERIAL          PRIMARY KEY,
    id_owner    INTEGER         REFERENCES Human(id),
    name        varchar(50)     NOT NULL,
    description varchar(100)
);

CREATE TABLE Task(
    id          SERIAL          PRIMARY KEY,
    id_tasklist INTEGER         REFERENCES TaskList(id),
    description varchar(100)    NOT NULL,
    duedate     timestamp,
    priority    INTEGER,
    status      INTEGER         DEFAULT 0,
    archived    boolean         DEFAULT FALSE
);

CREATE TABLE Category(
    id          SERIAL          PRIMARY KEY,
    id_owner    INTEGER         REFERENCES Human(id),
    description varchar(50)     NOT NULL,
    symbol      varchar(50),
    color       varchar(10)
);

CREATE TABLE TaskCategory(
    id_task     INTEGER         REFERENCES Task(id) ON DELETE CASCADE,
    id_category INTEGER         REFERENCES Category(id) ON DELETE CASCADE
);
