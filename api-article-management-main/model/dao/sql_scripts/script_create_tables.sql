CREATE TABLE USERS(
    username CHAR(8) NOT NULL,
    password VARCHAR(999) NOT NULL,
    role VARCHAR(25) NOT NULL,
    PRIMARY KEY(username),
    CONSTRAINT chk_role CHECK
        (role IN ('moderator', 'publisher'))
);

CREATE TABLE ARTICLE(
    article_id VARCHAR(10) NOT NULL,
    content VARCHAR(999) NOT NULL,
    date_de_publication DATE NOT NULL,
    author VARCHAR(8) NOT NULL,
    PRIMARY KEY(article_id),
    FOREIGN KEY(author) REFERENCES USERS(username)
);

CREATE TABLE LIKES(
    article_id VARCHAR(10) NOT NULL,
    id_username CHAR(8) NOT NULL,
    PRIMARY KEY(article_id, id_username),
    FOREIGN KEY(article_id) REFERENCES ARTICLE(article_id)
);

CREATE TABLE DISLIKES(
    article_id VARCHAR(10) NOT NULL,
    id_username CHAR(8) NOT NULL,
    PRIMARY KEY(article_id, id_username),
    FOREIGN KEY(article_id) REFERENCES ARTICLE(article_id)
);