<?php

function apis($r){
    $r->addRoute('GET', '/clearRedis', ['LoginController', 'clearRedis']);

    $r->addRoute('POST', '/login', ['LoginController', 'createJwt']);                                                       //1. 자체 로그인 API
    $r->addRoute('POST', '/email/valid', ['LoginController', 'isValidEmail']);                                              //1-2. 이메일 중복 확인 API
    $r->addRoute('GET', '/token', ['LoginController', 'decodeToken']);                                                      //1-3. 토큰 데이터 추출 API
    $r->addRoute('POST', '/token', ['LoginController', 'checkToken']);                                                      //1-4. 토큰 암호 유효성 검사 API
    $r->addRoute('GET', '/update', ['LoginController', 'checkUpdate']);                                                     //1-6. 강제 업데이트 API
    $r->addRoute('GET', '/popup/{version}', ['LoginController', 'getPopupRead']);                                           //1-9. 업데이트 공지 읽었는지 확인 API
    $r->addRoute('PATCH', '/popup/{version}', ['LoginController', 'updatePopupRead']);                                      //1-10. 업데이트 공지 읽기 API
    $r->addRoute('POST', '/email/check', ['LoginController', 'isMyEmail']);                                                 //1-11. 자신의 이메일인지 확인 API
    $r->addRoute('GET', '/terms', ['LoginController', 'getTerms']);                                                         //1-12. 약관 조회 API

    $r->addRoute('POST', '/user', ['LoginController', 'createUser']);                                                       //2. 자체 회원가입 API
    $r->addRoute('POST', '/user/{code:\d+}', ['LoginController', 'postUserInfo']);                                          //2-1. 간편 회원가입 API

    $r->addRoute('PATCH', '/name', ['UserController', 'updateName']);                                                       //4. 닉네임 변경 API
    $r->addRoute('PATCH', '/password', ['UserController', 'updatePassword']);
    $r->addRoute('DELETE', '/selfie', ['UserController', 'deleteSelfie']);                                                  //6-0. 프로필 사진 삭제 API
    $r->addRoute('PATCH', '/birth', ['UserController', 'updateBirth']);                                                     //6-1. 생년월일 변경 API
    $r->addRoute('GET', '/user', ['UserController', 'getUser']);                                                            //7. 회원정보조회 API
    $r->addRoute('GET', '/usercode/{userCode}/user', ['UserController', 'getUserByUserCode']);                              //7-0. 유저코드를 통한 회원정보 조회 API
    $r->addRoute('GET', '/user/stickers', ['StickerController', 'getUserStickers']);                                        //7-1. 유저 보유 스티커 조회 API(스티커 Controller에 존재)
    $r->addRoute('POST', '/user/searchPW', ['UserController', 'getTmpPw']);                                                 //7-2. 임시 비밀번호 발급

    $r->addRoute('POST', '/notification', ['NotificationController', 'checkNotification']);                                 //1-5. 알림 토큰 저장 API
    $r->addRoute('GET', '/alarm', ['NotificationController', 'checkAlarm']);                                                //1-7. 알림 허용 여부 확인 API
    $r->addRoute('PATCH', '/alarm', ['NotificationController', 'updateAlarm']);                                             //1-8. 알림 허용 여부 변경 API
    $r->addRoute('GET', '/alarm/ver2', ['NotificationController', 'checkAlarmVer2']);                                       //7-5. 알림 허용 여부 확인 API(3개)
    $r->addRoute('GET', '/alarm/time', ['NotificationController', 'getAlarmTime']);                                         //7-7. 알림 시간 조회 API

    $r->addRoute('POST', '/lock', ['LoginController', 'postLock']);
    $r->addRoute('DELETE', '/lock', ['LoginController', 'deleteLock']);
    $r->addRoute('GET', '/log', ['UserController', 'getLog']);                                                              //10. 알림 조회 API

    $r->addRoute('POST', '/diary', ['DiaryController', 'addDiary']);                                                        //11. 다이어리 추가 API
    $r->addRoute('POST', '/diaries/{diaryID:\d+}/user', ['DiaryController', 'addDiaryFriend']);                             //12. 다이어리 유저 추가 API
    $r->addRoute('GET', '/log/{diaryID:\d+}', ['DiaryController', 'getRequestByUserCode']);                                 //12-1. 유저코드 유저 조회 API
    $r->addRoute('DELETE', '/diary/{diaryID:\d+}', ['DiaryController', 'deleteDiary']);
    $r->addRoute('PATCH', '/diary', ['DiaryController', 'updateDiary']);
    $r->addRoute('GET', '/diaries', ['DiaryController', 'getDiaries']);                                                     //15. 다이어리 조회 API
    $r->addRoute('GET', '/diaries/{diaryID:\d+}/users', ['DiaryController', 'getDiariesMember']);                           //15-0. 다이어리 멤버 조회 API

    $r->addRoute('PATCH', '/notice', ['NoticeController', 'updateNotice']);                                                 //15-3. 다이어리 공지 수정 API
    $r->addRoute('GET', '/notice/{diaryID:\d+}', ['NoticeController', 'getNotice']);                                        //15-4. 다이어리 공지 조회 API

    // $r->addRoute('POST', '/post', ['PostController', 'addPostOld']);                                                        //16. 게시물 작성 API Deprecated
    // $r->addRoute('POST', '/post/ver2', ['PostController', 'addPost']);                                                      //16-1. 게시물 작성 API(이미지 추가) Deprecated
    $r->addRoute('POST', '/post/ver3', ['PostController', 'addPostVer3']);                                                  //16-2. 게시물 작성 API(날짜 폰트 추가)
    $r->addRoute('DELETE', '/post/{postID:\d+}', ['PostController', 'deletePost']);
    $r->addRoute('PATCH', '/post/ver3', ['PostController', 'updatePostVer3']);
    // $r->addRoute('PATCH', '/post', ['PostController', 'updatePostOld']);                                                    //18. 게시물 수정 API Deprecated
    $r->addRoute('PATCH', '/post/ver2', ['PostController', 'updatePost']);                                                  //18-1. 게시물 수정 API(이미지 추가)

    $r->addRoute('GET', '/diaries/{diaryID:\d+}/posts', ['PostController', 'getPostList']);                                 //19. 게시물 리스트 조회 API
    $r->addRoute('GET', '/diaries/{diaryID:\d+}/posts/ver2', ['PostController', 'getPostListNew']);                         //19-0. 게시물 리스트 조회 API(날짜)
    $r->addRoute('GET', '/diaries/{diaryID:\d+}/posts/countbydate', ['PostController', 'getPostNumByDate']);                //19-1. 게시물 날짜별 개수 조회 API
    // $r->addRoute('GET', '/posts/{postID:\d+}', ['PostController', 'getPostDetail']);                                        //20. 게시물 상세 조회 API Deprecated
    $r->addRoute('GET', '/posts/{postID:\d+}/ver2', ['PostController', 'getPostDetailVer2']);                               //20-1. 게시물 상세 조회 API(날짜 및 폰트 추가 버전)
    
    $r->addRoute('POST', '/posts/{postID:\d+}/stickers', ['StickerController', 'addSticker']);                              //22. 스티커 사용 API
    $r->addRoute('PATCH', '/posts/{postID:\d+}/stickers', ['StickerController', 'updateSticker']);                          //23. 스티커 수정 API
    $r->addRoute('GET', '/stickers/{stickerPackID:\d+}', ['StickerController', 'getStickerDetail']);                        //24-1. 스티커 상세 조회 API
    $r->addRoute('GET', '/store', ['StickerController', 'getStore']);                                                       //24-2. 스티커 상점 조회 API
    $r->addRoute('GET', '/posts/{postID:\d+}/stickers', ['StickerController', 'getStickerView']);                           //25. 사용자가 사용한 스티커 리스트 조회 API
    
    $r->addRoute('GET', '/points', ['PointController', 'getPoint']);                                                        //27. 포인트 조회 API

    $r->addRoute('POST', '/posts/{postID:\d+}/like', ['LikeCommentController', 'postLike']);                                //28. 좋아요 API
    $r->addRoute('POST', '/comment', ['LikeCommentController', 'postComment']);                                             //30. 댓글 작성 API
    $r->addRoute('DELETE', '/comment/{commentID:\d+}', ['LikeCommentController', 'deleteComment']);
    $r->addRoute('PATCH', '/comment', ['LikeCommentController', 'updateComment']);
    $r->addRoute('GET', '/posts/{postID:\d+}/comments', ['LikeCommentController', 'getComment']);                           //33. 댓글 리스트 조회 API
    
    $r->addRoute('GET', '/diaries/{diaryID:\d+}/schedule', ['CalenderController', 'getSchedule']);                          //37. 일정 조회 API

    $r->addRoute('GET', '/announcement', ['LoginController', 'getAnnouncement']);                                           //38. 공지사항 리스트 조회 API
    $r->addRoute('GET', '/announcement/{announcementID:\d+}', ['LoginController', 'getAnnouncementDetail']);                //39. 공지사항 상세 조회 API
    $r->addRoute('GET', '/announcement/check', ['LoginController', 'getAnnouncementCheck']);
}

function requires(){
    require './pdos/DiaryPdo.php';
    require './pdos/LikeCommentPdo.php';
    require './pdos/LoginPdo.php';
    require './pdos/NoticePdo.php';
    require './pdos/NotificationPdo.php';
    require './pdos/PostPdo.php';
    require './pdos/StickerPdo.php';
    require './pdos/UserPdo.php';
}