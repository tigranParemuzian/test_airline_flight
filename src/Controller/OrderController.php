<?php

namespace App\Controller;

use App\Entity\Flight;
use App\Entity\Payment;
use App\Form\FlightOrderType;
use App\Form\FlightType;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\FlightOrder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/{version<\d+>?1}/order", defaults={"version"=1}, requirements={"version"="\d+"})
 *
 * @SWG\Parameter(
 *     name="version",
 *     in="path",
 *     type="integer",
 *     default="1"
 * )
 * Class OrderController
 * @package App\Controller
 */
class OrderController extends AbstractController
{
    /**
     * @SWG\Tag(name="FlightOrder")
     *
     * @SWG\Parameter(
     *         name="Flight",
     *         in="body",
     *         description="Inserted Flight Order object.",
     *         required=false,
     *         type="object",
     *          @Model(type=FlightOrder::class, groups={"flight:order:add",
     *              "flight:order:client", "client:add",
     *              "flight:order:flight:attache"
     *     })
     *      ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *     type="object",
     *       ref=@Model(type=FlightOrder::class, groups={ "flight:order:read",
     *     "flight:order:flight", "flight:read",
     *     "flight:from:address", "flight:to:address", "address:address",
     *     "flight:order:client", "client:read"})
     *      )
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Something wrong with validation.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=409,
     *     description="Something wrong with system.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="message",
     *         type="string"
     *       )
     *     )
     * )
     *
     *
     * @Route("/booking", name="order-book", methods={"POST"})
     */
    public function booking(Request $request, ValidatorInterface $validator): Response
    {
//        try to get a entity manager
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        $flightOrder = new FlightOrder();
        $form = $this->createForm(FlightOrderType::class, $flightOrder, ['csrf_protection' => false]);

        $form->submit($data);
        // validate data and send message
        $errors = $validator->validate($flightOrder, null, ['flight-order-add', 'client-add']);
        $errorMessages = [];

        if(count($errors) >0) {
            foreach ($errors as $error) {

                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['messages'=>$errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $em->getConnection()->beginTransaction();
        $flightOrder->setStatus(FlightOrder::IS_BOOKED);
        $em->persist($flightOrder);

        try {

            $em->flush();
            $em->getConnection()->commit();

        }catch (\Exception $e) {
            $em->getConnection()->rollBack();
            return $this->json(['message'=>'Sorry booking not not available. Please try later.'], Response::HTTP_CONFLICT);
        }

        return $this->json($flightOrder, Response::HTTP_OK, [], ['groups' => FlightOrder::FLIGHT_ORDER_LIST]);
    }

    /**
     * @SWG\Tag(name="FlightOrder")
     *
     * @SWG\Parameter(
     *         name="Flight",
     *         in="body",
     *         description="Cancel Flight Order.",
     *         required=false,
     *         type="object",
     *          @Model(type=FlightOrder::class, groups={"flight:order:id"})
     *      ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *     type="object",
     *       ref=@Model(type=FlightOrder::class, groups={ "flight:order:read",
     *     "flight:order:flight", "flight:read",
     *     "flight:from:address", "flight:to:address", "address:address",
     *     "flight:order:client", "client:read"})
     *      )
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Something wrong with validation.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="Flight order not found.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=409,
     *     description="Something wrong with system.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="message",
     *         type="string"
     *       )
     *     )
     * )
     *
     *
     * @Route("/booking/cancel", name="order-book-cancel", methods={"POST"})
     */
    public function bookingCancel(Request $request, ValidatorInterface $validator): Response
    {

        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        if(array_key_exists('id', $data) === false || $data['id'] === null) {

            return $this->json(['messages'=>['id'=>'Flight Order id is required.']], Response::HTTP_BAD_REQUEST);

        }

        $flightOrder = $em->getRepository(FlightOrder::class)->find($data['id']);

        if(!$flightOrder) {
            return $this->json(['messages'=>sprintf('Flight Order by %s not found.', $data['id'])], Response::HTTP_NOT_FOUND);
        }

        $em->getConnection()->beginTransaction();

        $flightOrder->setStatus(FlightOrder::IS_BOOK_CANCELED);

        $em->persist($flightOrder);

        try {

            $em->flush();
            $em->getConnection()->commit();
        }catch (\Exception $e) {
            $em->getConnection()->rollBack();
            return $this->json(['message'=>'Sorry booking not not available. Please try later.'], Response::HTTP_CONFLICT);
        }

        return $this->json($flightOrder, Response::HTTP_OK, [], ['groups' => FlightOrder::FLIGHT_ORDER_LIST]);
    }

    /**
     * @SWG\Tag(name="FlightOrder")
     *
     * @SWG\Parameter(
     *         name="Flight",
     *         in="body",
     *         description="Buy ticket for Flight Order.",
     *         required=false,
     *         type="object",
     *          @Model(type=FlightOrder::class, groups={"flight:order:add", "flight:order:id",
     *              "flight:order:client", "client:add",
     *              "flight:order:flight:attache"
     *
     *     })
     *      ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *     type="object",
     *       ref=@Model(type=FlightOrder::class, groups={ "flight:order:read",
     *     "flight:order:flight", "flight:read",
     *     "flight:from:address", "flight:to:address", "address:address",
     *     "flight:order:client", "client:read"})
     *      )
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Something wrong with validation.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=409,
     *     description="Something wrong with system.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="message",
     *         type="string"
     *       )
     *     )
     * )
     *
     *
     * @Route("/ticket/buy", name="order-buy-ticket", methods={"POST"})
     */
    public function buyTicket(Request $request, ValidatorInterface $validator): Response
    {

        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $flightOrder = new FlightOrder();

        if(array_key_exists('id', $data) && !is_null($data['id'])) {

            $flightOrder = $em->getRepository(FlightOrder::class)->find($data['id']);


        }else{
            $form = $this->createForm(FlightOrderType::class, $flightOrder, ['csrf_protection' => false, 'definition'=>FlightOrder::IS_PAID]);

            $form->submit($data);
        }
        $em->getConnection()->beginTransaction();

        $payment = new Payment();
        $payment->setCost($flightOrder->getFlight()->getCost());
        $payment->setStatus(Payment::IS_PAID);
        $flightOrder->setStatus(FlightOrder::IS_PAID);
        $flightOrder->setPayment($payment);

        $errors = $validator->validate($flightOrder, null, ['flight-order-add', 'client-add', 'flight-order-paid', 'payment-add']);


        if(count($errors) >0) {

            $errorMessages = [];

            foreach ($errors as $error) {

                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['messages'=>$errorMessages], Response::HTTP_BAD_REQUEST);
        }


        $flightOrder->setStatus(FlightOrder::IS_BOOKED);
        $em->persist($flightOrder);

        try {

            $em->flush();
            $em->getConnection()->commit();
        }catch (\Exception $e) {
            $em->getConnection()->rollBack();
            return $this->json(['message'=>'Sorry booking not not available. Please try later.'], Response::HTTP_CONFLICT);
        }

        return $this->json($flightOrder, Response::HTTP_OK, [], ['groups' => FlightOrder::FLIGHT_ORDER_PAYMENT_LIST]);
    }

    /**
     * @SWG\Tag(name="FlightOrder")
     *
     * @SWG\Parameter(
     *         name="Flight",
     *         in="body",
     *         description="Cancel Flight Order.",
     *         required=false,
     *         type="object",
     *          @Model(type=FlightOrder::class, groups={"flight:order:id"})
     *      ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *     type="object",
     *       ref=@Model(type=FlightOrder::class, groups={ "flight:order:read",
     *     "flight:order:flight", "flight:read",
     *     "flight:from:address", "flight:to:address", "address:address",
     *     "flight:order:client", "client:read"})
     *      )
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Something wrong with validation.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="Flight order not found.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * ),
     * @SWG\Response(
     *     response=409,
     *     description="Something wrong with system.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="message",
     *         type="string"
     *       )
     *     )
     * )
     *
     *
     * @Route("/ticket/cancel", name="order-cancel-ticket", methods={"POST"})
     */
    public function cancelTicket(Request $request, ValidatorInterface $validator): Response
    {

        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        if(array_key_exists('id', $data) === false || $data['id'] === null) {

            return $this->json(['messages'=>['id'=>'Flight Order id is required.']], Response::HTTP_BAD_REQUEST);

        }

        $flightOrder = $em->getRepository(FlightOrder::class)->find($data['id']);

        if(!$flightOrder) {
            return $this->json(['messages'=>sprintf('Flight Order by %s not found.', $data['id'])], Response::HTTP_NOT_FOUND);
        }

        $em->getConnection()->beginTransaction();

        $flightOrder->setStatus(FlightOrder::IS_BOOK_CANCELED);

        $em->persist($flightOrder);

        try {

            $em->flush();
            $em->getConnection()->commit();
        }catch (\Exception $e) {
            $em->getConnection()->rollBack();
            return $this->json(['message'=>'Sorry booking not not available. Please try later.'], Response::HTTP_CONFLICT);
        }

        return $this->json($flightOrder, Response::HTTP_OK, [], ['groups' => FlightOrder::FLIGHT_ORDER_LIST]);
    }
}
